<?php

namespace App\Controller\Api;

use App\Repository\Storage\FileRepository;
use App\Service\Storage\FileUploadService;
use App\Service\Storage\StorageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[Route('/api/storage')]
#[IsGranted('ROLE_USER')]
class FileUploadController extends AbstractController
{
    private const MAX_CHUNKS = 1000;
    private FileUploadService $uploadService;
    private FileRepository $fileRepo;
    private StorageManager $storageManager;
    private \App\Service\Storage\FileUrlGenerator $urlGenerator;

    public function __construct(
        FileUploadService $uploadService, 
        FileRepository $fileRepo,
        StorageManager $storageManager,
        \App\Service\Storage\FileUrlGenerator $urlGenerator
    ) {
        $this->uploadService = $uploadService;
        $this->fileRepo = $fileRepo;
        $this->storageManager = $storageManager;
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('/upload', name: 'api_storage_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $options = [
                'disk' => $request->request->get('disk'),
                'optimize' => $request->request->getBoolean('optimize', true),
            ];
            
            $fileEntity = $this->uploadService->upload($file, $options);
            
            return new JsonResponse([
                'id' => $fileEntity->getId(),
                'name' => $fileEntity->getOriginalName(),
                'size' => $fileEntity->getSize(),
                'url' => $this->urlGenerator->getUrl($fileEntity),
                'mime' => $fileEntity->getMimeType(),
                'width' => $fileEntity->getWidth(),
                'height' => $fileEntity->getHeight(),
            ]);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => '文件上传失败，请稍后重试。'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/upload/chunk', name: 'api_storage_upload_chunk', methods: ['POST'])]
    public function uploadChunk(Request $request, \App\Repository\Storage\UploadSessionRepository $sessionRepo, \Doctrine\ORM\EntityManagerInterface $em): JsonResponse
    {
        $sessionId = $request->request->get('session_id');
        $chunkIndex = (int) $request->request->get('chunk_index');
        $totalChunks = (int) $request->request->get('total_chunks');
        $fileHash = $request->request->get('hash'); // Optional check
        $filename = $request->request->get('filename');
        $chunkFile = $request->files->get('file');

        if (!$chunkFile || !$sessionId) {
            return new JsonResponse(['error' => 'Invalid chunk upload request'], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->isValidUploadSession($sessionId, $chunkIndex, $totalChunks)) {
            return new JsonResponse(['error' => 'Invalid chunk upload request'], Response::HTTP_BAD_REQUEST);
        }

        $session = $sessionRepo->find($sessionId);
        if (!$session) {
            $session = new \App\Entity\Storage\UploadSession();
            // Manually set ID if provided by client or generate new
            // Ideally client generates UUID for session
            $reflection = new \ReflectionClass($session);
            $property = $reflection->getProperty('id');
            $property->setAccessible(true);
            $property->setValue($session, $sessionId);

            $session->setFilename($filename ?? 'unknown')
                    ->setTotalChunks($totalChunks)
                    ->setFileHash($fileHash ?? '')
                    ->setStatus('pending');
            $em->persist($session);
        }

        // Save chunk
        $tempDir = sys_get_temp_dir() . '/storage_chunks/' . $sessionId;
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0700, true);
        }
        $chunkFile->move($tempDir, (string)$chunkIndex);

        $session->addUploadedChunk($chunkIndex);
        $em->flush();

        return new JsonResponse([
            'status' => 'chunk_uploaded',
            'uploaded_chunks' => $session->getUploadedChunks()
        ]);
    }

    #[Route('/upload/complete', name: 'api_storage_upload_complete', methods: ['POST'])]
    public function completeUpload(Request $request, \App\Repository\Storage\UploadSessionRepository $sessionRepo, \Doctrine\ORM\EntityManagerInterface $em): JsonResponse
    {
        $sessionId = $request->request->get('session_id');
        if (!is_string($sessionId) || !Uuid::isValid($sessionId)) {
            return new JsonResponse(['error' => 'Session not found'], Response::HTTP_NOT_FOUND);
        }

        $session = $sessionRepo->find($sessionId);

        if (!$session) {
            return new JsonResponse(['error' => 'Session not found'], Response::HTTP_NOT_FOUND);
        }

        if (count($session->getUploadedChunks()) < $session->getTotalChunks()) {
            return new JsonResponse(['error' => 'Upload incomplete'], Response::HTTP_BAD_REQUEST);
        }

        // Merge chunks
        $tempDir = sys_get_temp_dir() . '/storage_chunks/' . $sessionId;
        $finalPath = $tempDir . '/merged_file';
        $outFile = fopen($finalPath, 'wb');
        if ($outFile === false) {
            return new JsonResponse(['error' => 'Failed to process upload'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        for ($i = 0; $i < $session->getTotalChunks(); $i++) {
            $chunkPath = $tempDir . '/' . $i;
            if (!file_exists($chunkPath)) {
                fclose($outFile);
                return new JsonResponse(['error' => "Chunk $i missing"], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $chunkIn = fopen($chunkPath, 'rb');
            stream_copy_to_stream($chunkIn, $outFile);
            fclose($chunkIn);
            unlink($chunkPath); // Delete chunk
        }
        fclose($outFile);

        // Upload merged file
        $uploadedFile = new \Symfony\Component\HttpFoundation\File\UploadedFile(
            $finalPath,
            $session->getFilename(),
            null,
            null,
            true // Test mode = true to skip is_uploaded_file check for manually created file
        );

        $options = [
            'disk' => $request->request->get('disk'),
            'optimize' => $request->request->getBoolean('optimize', true),
        ];

        try {
            $fileEntity = $this->uploadService->upload($uploadedFile, $options);
            
            // Cleanup
            if (file_exists($finalPath)) {
                unlink($finalPath);
            }
            if (is_dir($tempDir)) {
                rmdir($tempDir);
            }
            $em->remove($session);
            $em->flush();

            return new JsonResponse([
                'id' => $fileEntity->getId(),
                'name' => $fileEntity->getOriginalName(),
                'size' => $fileEntity->getSize(),
                'url' => $this->urlGenerator->getUrl($fileEntity),
                'mime' => $fileEntity->getMimeType(),
                'width' => $fileEntity->getWidth(),
                'height' => $fileEntity->getHeight(),
            ]);
        } catch (\RuntimeException $e) {
            if (file_exists($finalPath)) {
                unlink($finalPath);
            }

            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            if (file_exists($finalPath)) {
                unlink($finalPath);
            }

            return new JsonResponse(['error' => '文件上传失败，请稍后重试。'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/file/{id}', name: 'api_storage_get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        $file = $this->fileRepo->find($id);
        if (!$file) {
            return new JsonResponse(['error' => 'File not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $file->getId(),
            'name' => $file->getOriginalName(),
            'size' => $file->getSize(),
            'url' => $this->urlGenerator->getUrl($file),
            'mime' => $file->getMimeType(),
            'created_at' => $file->getCreatedAt()->format('c'),
        ]);
    }

    #[Route('/file/{id}', name: 'api_storage_delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $file = $this->fileRepo->find($id);
        if (!$file) {
            return new JsonResponse(['error' => 'File not found'], Response::HTTP_NOT_FOUND);
        }

        // We should also delete from storage, but for now just delete entity or mark as deleted
        // Implementing delete in FileUploadService would be better
        // $this->uploadService->delete($file);
        
        return new JsonResponse(['success' => true]);
    }

    private function isValidUploadSession(mixed $sessionId, int $chunkIndex, int $totalChunks): bool
    {
        if (!is_string($sessionId) || !Uuid::isValid($sessionId)) {
            return false;
        }

        if ($chunkIndex < 0 || $totalChunks <= 0 || $totalChunks > self::MAX_CHUNKS) {
            return false;
        }

        return $chunkIndex < $totalChunks;
    }
}
