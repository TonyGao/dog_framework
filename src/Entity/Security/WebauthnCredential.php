<?php

namespace App\Entity\Security;

use App\Repository\Security\WebauthnCredentialRepository;
use Doctrine\ORM\Mapping as ORM;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\CertificateTrustPath;
use Webauthn\TrustPath\EmptyTrustPath;
use Webauthn\TrustPath\TrustPathLoader;
use Symfony\Component\Uid\Uuid;
use App\Entity\Organization\Employee;

#[ORM\Entity(repositoryClass: WebauthnCredentialRepository::class)]
#[ORM\Table(name: 'webauthn_credentials')]
class WebauthnCredential
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $publicKeyCredentialId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $type;

    #[ORM\Column(type: 'json')]
    private array $transports;

    #[ORM\Column(type: 'string', length: 255)]
    private string $attestationType;

    #[ORM\Column(type: 'json')]
    private array $trustPath;

    #[ORM\Column(type: 'uuid')]
    private Uuid $aaguid;

    #[ORM\Column(type: 'text')]
    private string $credentialPublicKey;

    #[ORM\Column(type: 'string', length: 255)]
    private string $userHandle;

    #[ORM\Column(type: 'integer')]
    private int $counter;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $otherUI = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $backupEligible = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $backupStatus = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $uvInitialized = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastUsedAt = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $deviceName = null;

    #[ORM\ManyToOne(targetEntity: Employee::class, inversedBy: 'passkeys')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Employee $employee = null;

    public function __construct(
        string $publicKeyCredentialId,
        string $type,
        array $transports,
        string $attestationType,
        array $trustPath,
        Uuid $aaguid,
        string $credentialPublicKey,
        string $userHandle,
        int $counter
    ) {
        $this->id = $this->base64UrlEncode($publicKeyCredentialId);
        $this->publicKeyCredentialId = $this->base64UrlEncode($publicKeyCredentialId);
        $this->type = $type;
        $this->transports = $transports;
        $this->attestationType = $attestationType;
        $this->trustPath = $trustPath;
        $this->aaguid = $aaguid;
        $this->credentialPublicKey = $this->base64UrlEncode($credentialPublicKey);
        $this->userHandle = $this->base64UrlEncode($userHandle);
        $this->counter = $counter;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPublicKeyCredentialSource(): PublicKeyCredentialSource
    {
        $trustPathData = $this->trustPath;
        if (isset($trustPathData['x5c'])) {
            $trustPath = CertificateTrustPath::create($trustPathData['x5c']);
        } else {
            $trustPath = EmptyTrustPath::create();
        }

        return new PublicKeyCredentialSource(
            $this->base64UrlDecode($this->publicKeyCredentialId),
            $this->type,
            $this->transports,
            $this->attestationType,
            $trustPath,
            $this->aaguid,
            $this->base64UrlDecode($this->credentialPublicKey),
            $this->base64UrlDecode($this->userHandle),
            $this->counter,
            $this->otherUI,
            $this->backupEligible,
            $this->backupStatus,
            $this->uvInitialized
        );
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public function setEmployee(?Employee $employee): self
    {
        $this->employee = $employee;
        return $this;
    }

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    // Setters for updatable fields
    public function setCounter(int $counter): self
    {
        $this->counter = $counter;
        return $this;
    }

    public function setOtherUI(?array $otherUI): self
    {
        $this->otherUI = $otherUI;
        return $this;
    }

    public function setBackupEligible(?bool $backupEligible): self
    {
        $this->backupEligible = $backupEligible;
        return $this;
    }

    public function setBackupStatus(?bool $backupStatus): self
    {
        $this->backupStatus = $backupStatus;
        return $this;
    }

    public function setUvInitialized(?bool $uvInitialized): self
    {
        $this->uvInitialized = $uvInitialized;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getLastUsedAt(): ?\DateTimeInterface
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(?\DateTimeInterface $lastUsedAt): self
    {
        $this->lastUsedAt = $lastUsedAt;
        return $this;
    }

    public function getDeviceName(): ?string
    {
        return $this->deviceName;
    }

    public function setDeviceName(?string $deviceName): self
    {
        $this->deviceName = $deviceName;
        return $this;
    }

    public function getAaguid(): Uuid
    {
        return $this->aaguid;
    }
}
