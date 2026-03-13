<?php

namespace App\Controller\Api;

use App\Entity\Organization\Employee;
use App\Service\Storage\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/employee')]
class AvatarController extends AbstractController
{
    private FileUploadService $fileUploadService;
    private EntityManagerInterface $em;

    public function __construct(FileUploadService $fileUploadService, EntityManagerInterface $em)
    {
        $this->fileUploadService = $fileUploadService;
        $this->em = $em;
    }

    #[Route('/{id}/avatar', name: 'api_employee_avatar_upload', methods: ['POST'])]
    public function upload(string $id, Request $request): JsonResponse
    {
        // Find employee
        $employee = $this->em->getRepository(Employee::class)->find($id);
        if (!$employee) {
            return new JsonResponse(['error' => 'Employee not found'], 404);
        }

        $file = $request->files->get('avatar');
        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], 400);
        }

        try {
            $uploadedFile = $this->fileUploadService->upload($file, [
                'optimize' => true,
            ]);

            // Assuming local storage for now, construct URL.
            // In a real app, use a service to resolve URLs from paths/disks.
            $url = '/uploads/' . $uploadedFile->getPath();
            
            $employee->setAvatar($url);
            $this->em->flush();

            return new JsonResponse([
                'success' => true,
                'url' => $url,
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
