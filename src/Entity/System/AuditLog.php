<?php

namespace App\Entity\System;

use App\Repository\System\AuditLogRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\Table(name: 'sys_audit_log')]
class AuditLog
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $operatorId = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $operatorType = null; // 'employee' or 'system_user'

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $action = null; // e.g., 'login', 'create', 'update', 'delete'

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $target = null; // e.g., 'User', 'Config'

    #[ORM\Column(type: 'json')]
    private array $details = []; // Store old/new values or other metadata

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getOperatorId(): ?string
    {
        return $this->operatorId;
    }

    public function setOperatorId(?string $operatorId): self
    {
        $this->operatorId = $operatorId;
        return $this;
    }

    public function getOperatorType(): ?string
    {
        return $this->operatorType;
    }

    public function setOperatorType(string $operatorType): self
    {
        $this->operatorType = $operatorType;
        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;
        return $this;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function setDetails(array $details): self
    {
        $this->details = $details;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
