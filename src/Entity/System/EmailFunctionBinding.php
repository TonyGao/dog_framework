<?php

namespace App\Entity\System;

use App\Repository\System\EmailFunctionBindingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailFunctionBindingRepository::class)]
class EmailFunctionBinding
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $functionCode = null;

    #[ORM\Column(length: 255)]
    private ?string $functionName = null;

    #[ORM\ManyToOne(targetEntity: EmailTemplate::class)]
    private ?EmailTemplate $emailTemplate = null;

    #[ORM\ManyToOne(targetEntity: EmailConfig::class)]
    private ?EmailConfig $emailConfig = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFunctionCode(): ?string
    {
        return $this->functionCode;
    }

    public function setFunctionCode(string $functionCode): static
    {
        $this->functionCode = $functionCode;

        return $this;
    }

    public function getFunctionName(): ?string
    {
        return $this->functionName;
    }

    public function setFunctionName(string $functionName): static
    {
        $this->functionName = $functionName;

        return $this;
    }

    public function getEmailTemplate(): ?EmailTemplate
    {
        return $this->emailTemplate;
    }

    public function setEmailTemplate(?EmailTemplate $emailTemplate): static
    {
        $this->emailTemplate = $emailTemplate;

        return $this;
    }

    public function getEmailConfig(): ?EmailConfig
    {
        return $this->emailConfig;
    }

    public function setEmailConfig(?EmailConfig $emailConfig): static
    {
        $this->emailConfig = $emailConfig;

        return $this;
    }
}
