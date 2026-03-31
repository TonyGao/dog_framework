<?php

namespace App\Service\Storage;

use App\Entity\Storage\File;
use App\Repository\Storage\FileRepository;
use App\Event\Storage\FileUploadedEvent;
use App\Event\Storage\FileUploadingEvent;
use App\Message\Storage\ImageCompressMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Uid\Uuid;

class FileUploadService
{
    private const DEFAULT_MAX_FILE_SIZE = 52428800;
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'ico',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'rtf',
        'zip', 'rar', '7z', 'tar', 'gz',
        'mp3', 'mp4', 'wav', 'avi', 'mov', 'webm'
    ];
    
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/x-icon',
        'application/pdf', 
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain', 'text/csv', 'application/rtf',
        'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar', 'application/gzip',
        'audio/mpeg', 'video/mp4', 'audio/wav', 'video/x-msvideo', 'video/quicktime', 'video/webm'
    ];
    private StorageManager $storageManager;
    private EntityManagerInterface $em;
    private FileRepository $fileRepo;
    private EventDispatcherInterface $dispatcher;
    private MessageBusInterface $bus;
    private ImageOptimizerService $imageOptimizer;

    public function __construct(
        StorageManager $storageManager,
        EntityManagerInterface $em,
        FileRepository $fileRepo,
        EventDispatcherInterface $dispatcher,
        MessageBusInterface $bus,
        ImageOptimizerService $imageOptimizer
    ) {
        $this->storageManager = $storageManager;
        $this->em = $em;
        $this->fileRepo = $fileRepo;
        $this->dispatcher = $dispatcher;
        $this->bus = $bus;
        $this->imageOptimizer = $imageOptimizer;
    }

    public function upload(UploadedFile $uploadedFile, array $options = []): File
    {
        // Dispatch uploading event
        $uploadingEvent = new FileUploadingEvent($uploadedFile, $options);
        $this->dispatcher->dispatch($uploadingEvent, FileUploadingEvent::NAME);
        $options = $uploadingEvent->getOptions();
        $this->assertFileIsSafe($uploadedFile, $options);

        $hash = hash_file('sha256', $uploadedFile->getPathname());
        
        // Deduplication check
        $existingFile = $this->fileRepo->findByHash($hash);
        if ($existingFile) {
            return $existingFile;
        }

        $disk = $options['disk'] ?? null;
        $adapter = $this->storageManager->getAdapter($disk);
        $config = $this->storageManager->getStorageConfig($disk);
        $mimeType = (string) ($uploadedFile->getMimeType() ?? 'application/octet-stream');

        // Generate unique path
        $extension = $uploadedFile->guessExtension() ?? 'bin';
        
        $strategy = 'Y/m/d';
        if ($config && isset($config->getConfig()['directory_strategy'])) {
            $strategy = $config->getConfig()['directory_strategy'];
        }

        if ($strategy === 'none') {
            $filename = sprintf('%s.%s', Uuid::v4()->toRfc4122(), $extension);
        } else {
            $filename = sprintf('%s/%s.%s', date($strategy), Uuid::v4()->toRfc4122(), $extension);
        }
        
        // Optimize image before upload if requested
        if (str_starts_with($mimeType, 'image/') && ($options['optimize'] ?? false)) {
            // Processing happens here or async. 
            // For now, let's just upload raw and dispatch async optimization.
        }

        // Upload to storage
        $stream = fopen($uploadedFile->getPathname(), 'r');
        if ($stream === false) {
            throw new \RuntimeException('无法读取上传文件。');
        }

        try {
            $contents = stream_get_contents($stream);
        } finally {
            fclose($stream);
        }

        if ($contents === false) {
            throw new \RuntimeException('无法读取上传文件内容。');
        }

        $adapter->upload($filename, $contents);

        // Get dimensions if image
        $width = null;
        $height = null;
        if (str_starts_with($mimeType, 'image/')) {
            [$width, $height] = $this->imageOptimizer->getDimensions($uploadedFile->getPathname());
        }

        // Create Entity
        $file = new File();
        $file->setDisk($disk ?? 'default')
             ->setPath($filename)
             ->setOriginalName($uploadedFile->getClientOriginalName())
             ->setMimeType($mimeType)
             ->setSize($uploadedFile->getSize())
             ->setHash($hash)
             ->setWidth($width)
             ->setHeight($height);

        $this->em->persist($file);
        $this->em->flush();

        // Dispatch Event
        $this->dispatcher->dispatch(new FileUploadedEvent($file), FileUploadedEvent::NAME);

        // Dispatch Async Optimization
        if (str_starts_with($file->getMimeType(), 'image/')) {
            $this->bus->dispatch(new ImageCompressMessage($file->getId()));
        }

        return $file;
    }

    private function assertFileIsSafe(UploadedFile $uploadedFile, array $options): void
    {
        $size = (int) $uploadedFile->getSize();
        if ($size <= 0) {
            throw new \RuntimeException('上传文件不能为空。');
        }

        $maxSize = (int) ($options['max_size'] ?? self::DEFAULT_MAX_FILE_SIZE);
        if ($size > $maxSize) {
            throw new \RuntimeException('上传文件超过大小限制。');
        }

        $clientExtension = mb_strtolower((string) pathinfo($uploadedFile->getClientOriginalName(), \PATHINFO_EXTENSION));
        $guessedExtension = mb_strtolower((string) ($uploadedFile->guessExtension() ?? ''));
        
        $allowedExts = $options['allowed_extensions'] ?? self::ALLOWED_EXTENSIONS;
        
        $extensionValid = false;
        foreach ([$clientExtension, $guessedExtension] as $extension) {
            if ($extension !== '' && in_array($extension, $allowedExts, true)) {
                $extensionValid = true;
                break;
            }
        }
        
        if (!$extensionValid) {
            throw new \RuntimeException('上传文件类型不被允许，请上传允许的文件类型。');
        }

        $mimeType = mb_strtolower((string) ($uploadedFile->getMimeType() ?? ''));
        $allowedMimes = $options['allowed_mime_types'] ?? self::ALLOWED_MIME_TYPES;
        
        if ($mimeType === '' || !in_array($mimeType, $allowedMimes, true)) {
            throw new \RuntimeException('上传文件类型不被允许，请上传允许的文件类型。');
        }
    }
}
