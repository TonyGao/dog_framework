<?php

namespace App\Entity\Storage;

use App\Repository\Storage\StorageConfigRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StorageConfigRepository::class)]
#[ORM\Table(name: 'storage_configs')]
class StorageConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $adapterType = null; // local, s3

    #[ORM\Column(type: 'boolean')]
    private bool $isDefault = false;

    #[ORM\Column(type: 'json')]
    private array $config = [];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $cdnDomain = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getAdapterType(): ?string
    {
        return $this->adapterType;
    }

    public function setAdapterType(string $adapterType): self
    {
        $this->adapterType = $adapterType;
        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    public function getCdnDomain(): ?string
    {
        return $this->cdnDomain;
    }

    public function setCdnDomain(?string $cdnDomain): self
    {
        $this->cdnDomain = $cdnDomain;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
