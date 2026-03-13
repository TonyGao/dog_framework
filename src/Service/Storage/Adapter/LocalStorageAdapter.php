<?php

namespace App\Service\Storage\Adapter;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class LocalStorageAdapter implements StorageAdapterInterface
{
    private Filesystem $filesystem;
    private string $publicUrl;

    public function __construct(string $rootPath, string $publicUrl)
    {
        $adapter = new LocalFilesystemAdapter($rootPath);
        $this->filesystem = new Filesystem($adapter);
        $this->publicUrl = rtrim($publicUrl, '/');
    }

    public function upload(string $path, string $contents, array $config = []): array
    {
        $this->filesystem->write($path, $contents, $config);
        
        return [
            'path' => $path,
            'size' => $this->fileSize($path),
            'mime_type' => $this->mimeType($path),
        ];
    }

    public function delete(string $path): bool
    {
        try {
            $this->filesystem->delete($path);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function exists(string $path): bool
    {
        return $this->filesystem->has($path);
    }

    public function getUrl(string $path): string
    {
        return $this->publicUrl . '/' . ltrim($path, '/');
    }

    public function getTemporaryUrl(string $path, \DateTimeInterface $expiresAt): string
    {
        // Local storage doesn't support signed URLs natively without a dedicated controller
        // For now, return public URL or implement a signed route logic in Controller
        return $this->getUrl($path);
    }

    public function readStream(string $path)
    {
        return $this->filesystem->readStream($path);
    }

    public function mimeType(string $path): string
    {
        return $this->filesystem->mimeType($path);
    }

    public function fileSize(string $path): int
    {
        return $this->filesystem->fileSize($path);
    }
}
