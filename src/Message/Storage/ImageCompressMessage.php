<?php

namespace App\Message\Storage;

class ImageCompressMessage
{
    private string $fileId;

    public function __construct(string $fileId)
    {
        $this->fileId = $fileId;
    }

    public function getFileId(): string
    {
        return $this->fileId;
    }
}
