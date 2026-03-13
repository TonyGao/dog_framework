<?php

namespace App\Event\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\EventDispatcher\Event;

class FileUploadingEvent extends Event
{
    public const NAME = 'storage.file.uploading';

    private UploadedFile $uploadedFile;
    private array $options;

    public function __construct(UploadedFile $uploadedFile, array $options)
    {
        $this->uploadedFile = $uploadedFile;
        $this->options = $options;
    }

    public function getUploadedFile(): UploadedFile
    {
        return $this->uploadedFile;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }
}
