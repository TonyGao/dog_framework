<?php

namespace App\Entity\Security;

use App\Entity\Traits\CommonTrait;
use App\Repository\Security\PasswordResetMethodRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PasswordResetMethodRepository::class)]
#[ORM\Table(name: 'security_password_reset_method')]
#[ORM\HasLifecycleCallbacks]
class PasswordResetMethod
{
    use CommonTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private $id;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private $methodKey;

    #[ORM\Column(type: 'string', length: 100)]
    private $name;

    #[ORM\Column(type: 'integer')]
    private $priority = 0;

    #[ORM\Column(type: 'boolean')]
    private $isEnabled = true;

    #[ORM\Column(type: 'json', nullable: true)]
    private $config = [];

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getMethodKey(): ?string
    {
        return $this->methodKey;
    }

    public function setMethodKey(string $methodKey): self
    {
        $this->methodKey = $methodKey;

        return $this;
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

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function isIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function getConfig(): ?array
    {
        return $this->config;
    }

    public function setConfig(?array $config): self
    {
        $this->config = $config;

        return $this;
    }
}
