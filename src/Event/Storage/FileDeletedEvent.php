<?php

namespace App\Event\Storage;

use App\Entity\Storage\File;
use Symfony\Contracts\EventDispatcher\Event;

class FileDeletedEvent extends Event
{
    public const NAME = 'storage.file.deleted';

    private File $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function getFile(): File
    {
        return $this->file;
    }
}
