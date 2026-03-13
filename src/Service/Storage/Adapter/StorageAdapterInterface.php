<?php

namespace App\Service\Storage\Adapter;

use Symfony\Component\HttpFoundation\File\File;

interface StorageAdapterInterface
{
    /**
     * Upload a file to storage
     *
     * @param string $path Target path
     * @param string $contents File contents or stream
     * @param array $config Additional configuration (visibility, etc.)
     * @return array Metadata of the uploaded file
     */
    public function upload(string $path, string $contents, array $config = []): array;

    /**
     * Delete a file
     */
    public function delete(string $path): bool;

    /**
     * Check if file exists
     */
    public function exists(string $path): bool;

    /**
     * Get public URL
     */
    public function getUrl(string $path): string;

    /**
     * Get temporary signed URL (for private files)
     */
    public function getTemporaryUrl(string $path, \DateTimeInterface $expiresAt): string;

    /**
     * Get file stream
     * @return resource
     */
    public function readStream(string $path);
    
    /**
     * Get file mime type
     */
    public function mimeType(string $path): string;

    /**
     * Get file size
     */
    public function fileSize(string $path): int;
}
