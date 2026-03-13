<?php

namespace App\Entity\Storage;

use App\Repository\Storage\UploadSessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UploadSessionRepository::class)]
#[ORM\Table(name: 'storage_upload_sessions')]
class UploadSession
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $filename = null;

    #[ORM\Column(type: 'string', length: 64)]
    private ?string $fileHash = null;

    #[ORM\Column(type: 'integer')]
    private ?int $totalChunks = null;

    #[ORM\Column(type: 'json')]
    private array $uploadedChunks = [];

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $status = 'pending'; // pending, completed, expired

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $expiresAt = null;

    public function __construct()
    {
        $this->id = uuid_create();
        $this->createdAt = new \DateTimeImmutable();
        $this->expiresAt = $this->createdAt->modify('+24 hours');
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    public function getFileHash(): ?string
    {
        return $this->fileHash;
    }

    public function setFileHash(string $fileHash): self
    {
        $this->fileHash = $fileHash;
        return $this;
    }

    public function getTotalChunks(): ?int
    {
        return $this->totalChunks;
    }

    public function setTotalChunks(int $totalChunks): self
    {
        $this->totalChunks = $totalChunks;
        return $this;
    }

    public function getUploadedChunks(): array
    {
        return $this->uploadedChunks;
    }

    public function addUploadedChunk(int $chunkIndex): self
    {
        if (!in_array($chunkIndex, $this->uploadedChunks)) {
            $this->uploadedChunks[] = $chunkIndex;
            sort($this->uploadedChunks);
        }
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
