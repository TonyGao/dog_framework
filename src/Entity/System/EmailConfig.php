<?php

namespace App\Entity\System;

use App\Repository\System\EmailConfigRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\CommonTraitWithoutOrg;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EmailConfigRepository::class)]
#[ORM\Table(name: 'sys_email_config')]
class EmailConfig
{
    use CommonTraitWithoutOrg;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isDefault = false;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $protocol = 'smtp';

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $host = null;

    #[ORM\Column(type: 'integer')]
    private ?int $port = 465;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $encryption = 'ssl';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $senderName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $senderAddress = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getIsDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function setProtocol(string $protocol): self
    {
        $this->protocol = $protocol;
        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(?int $port): self
    {
        $this->port = $port;
        return $this;
    }

    public function getEncryption(): ?string
    {
        return $this->encryption;
    }

    public function setEncryption(?string $encryption): self
    {
        $this->encryption = $encryption;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(?string $senderName): self
    {
        $this->senderName = $senderName;
        return $this;
    }

    public function getSenderAddress(): ?string
    {
        return $this->senderAddress;
    }

    public function setSenderAddress(?string $senderAddress): self
    {
        $this->senderAddress = $senderAddress;
        return $this;
    }
}
