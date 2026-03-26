<?php

namespace App\Entity\System;

use App\Repository\System\EmailTemplateRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\CommonTraitWithoutOrg;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EmailTemplateRepository::class)]
#[ORM\Table(name: 'sys_email_template')]
class EmailTemplate
{
    use CommonTraitWithoutOrg;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private ?string $code = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $subject = null;

    #[ORM\Column(type: 'text')]
    private ?string $bodyHtml = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: EmailConfig::class)]
    #[ORM\JoinColumn(name: 'email_config_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?EmailConfig $emailConfig = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
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

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getBodyHtml(): ?string
    {
        return $this->bodyHtml;
    }

    public function setBodyHtml(string $bodyHtml): self
    {
        $this->bodyHtml = $bodyHtml;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getEmailConfig(): ?EmailConfig
    {
        return $this->emailConfig;
    }

    public function setEmailConfig(?EmailConfig $emailConfig): self
    {
        $this->emailConfig = $emailConfig;
        return $this;
    }
}
