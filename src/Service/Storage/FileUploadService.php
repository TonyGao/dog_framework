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

class FileUploadService
{
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

        $hash = hash_file('sha256', $uploadedFile->getPathname());
        
        // Deduplication check
        $existingFile = $this->fileRepo->findByHash($hash);
        if ($existingFile) {
            return $existingFile;
        }

        $disk = $options['disk'] ?? null;
        $adapter = $this->storageManager->getAdapter($disk);
        $config = $this->storageManager->getStorageConfig($disk);

        // Generate unique path
        $extension = $uploadedFile->guessExtension() ?? 'bin';
        
        $strategy = 'Y/m/d';
        if ($config && isset($config->getConfig()['directory_strategy'])) {
            $strategy = $config->getConfig()['directory_strategy'];
        }

        if ($strategy === 'none') {
            $filename = sprintf('%s.%s', uuid_create(), $extension);
        } else {
            $filename = sprintf('%s/%s.%s', date($strategy), uuid_create(), $extension);
        }
        
        // Optimize image before upload if requested
        if (str_starts_with($uploadedFile->getMimeType(), 'image/') && ($options['optimize'] ?? false)) {
            // Processing happens here or async. 
            // For now, let's just upload raw and dispatch async optimization.
        }

        // Upload to storage
        $stream = fopen($uploadedFile->getPathname(), 'r');
        $adapter->upload($filename, stream_get_contents($stream)); // Simple upload for now
        fclose($stream);

        // Get dimensions if image
        $width = null;
        $height = null;
        if (str_starts_with($uploadedFile->getMimeType(), 'image/')) {
            [$width, $height] = $this->imageOptimizer->getDimensions($uploadedFile->getPathname());
        }

        // Create Entity
        $file = new File();
        $file->setDisk($disk ?? 'default')
             ->setPath($filename)
             ->setOriginalName($uploadedFile->getClientOriginalName())
             ->setMimeType($uploadedFile->getMimeType())
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
}
