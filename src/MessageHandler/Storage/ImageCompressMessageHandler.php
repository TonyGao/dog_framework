<?php

namespace App\MessageHandler\Storage;

use App\Message\Storage\ImageCompressMessage;
use App\Repository\Storage\FileRepository;
use App\Service\Storage\ImageOptimizerService;
use App\Service\Storage\StorageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ImageCompressMessageHandler
{
    private FileRepository $fileRepo;
    private StorageManager $storageManager;
    private ImageOptimizerService $optimizer;
    private EntityManagerInterface $em;

    public function __construct(
        FileRepository $fileRepo,
        StorageManager $storageManager,
        ImageOptimizerService $optimizer,
        EntityManagerInterface $em
    ) {
        $this->fileRepo = $fileRepo;
        $this->storageManager = $storageManager;
        $this->optimizer = $optimizer;
        $this->em = $em;
    }

    public function __invoke(ImageCompressMessage $message)
    {
        $file = $this->fileRepo->find($message->getFileId());
        if (!$file) {
            return;
        }

        // Logic to download, optimize, and re-upload would go here
        // For simplicity, we'll just log or update metadata
        // In a real scenario:
        // 1. Download file to temp
        // 2. Optimize
        // 3. Upload optimized version (maybe as a variant or replace)
        // 4. Update File entity size/width/height
        
        // This is a placeholder for the actual async logic
    }
}
