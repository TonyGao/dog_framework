<?php

namespace App\Repository\Security;

use App\Entity\Organization\Employee;
use App\Entity\Security\WebauthnCredential;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Webauthn\Bundle\Repository\CanSaveCredentialSource;
use Webauthn\Bundle\Repository\PublicKeyCredentialSourceRepositoryInterface;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\TrustPath\CertificateTrustPath;
use Webauthn\TrustPath\EmptyTrustPath;
use Webauthn\TrustPath\TrustPath;

class WebauthnCredentialRepository extends ServiceEntityRepository implements PublicKeyCredentialSourceRepositoryInterface, CanSaveCredentialSource
{
    public function __construct(ManagerRegistry $registry, private \Psr\Log\LoggerInterface $logger)
    {
        parent::__construct($registry, WebauthnCredential::class);
    }

    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        // Encode the credential ID to match the stored format
        $encodedId = $this->base64UrlEncode($publicKeyCredentialId);
        $this->logger->info('WebAuthn: Finding credential by ID', ['id' => $encodedId, 'raw' => bin2hex($publicKeyCredentialId)]);

        /** @var WebauthnCredential|null $credential */
        $credential = $this->findOneBy(['publicKeyCredentialId' => $encodedId]);

        if (!$credential) {
            $this->logger->warning('WebAuthn: Credential not found', ['id' => $encodedId]);
            return null;
        }

        $this->logger->info('WebAuthn: Credential found', ['id' => $encodedId, 'userHandle' => $credential->getPublicKeyCredentialSource()->userHandle]);
        return $credential->getPublicKeyCredentialSource();
    }

    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        // Encode the user handle to match the stored format
        $encodedUserHandle = $this->base64UrlEncode($publicKeyCredentialUserEntity->id);

        /** @var WebauthnCredential[] $credentials */
        $credentials = $this->findBy(['userHandle' => $encodedUserHandle]);

        $sources = [];
        foreach ($credentials as $credential) {
            $sources[] = $credential->getPublicKeyCredentialSource();
        }

        return $sources;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }


    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        $entityManager = $this->getEntityManager();

        $encodedId = $this->base64UrlEncode($publicKeyCredentialSource->publicKeyCredentialId);
        /** @var WebauthnCredential|null $existingCredential */
        $existingCredential = $this->findOneBy(['publicKeyCredentialId' => $encodedId]);
        
        if ($existingCredential) {
            $credential = $existingCredential;
            $credential->setCounter($publicKeyCredentialSource->counter);
            $credential->setOtherUI($publicKeyCredentialSource->otherUI);
            $credential->setBackupEligible($publicKeyCredentialSource->backupEligible);
            $credential->setBackupStatus($publicKeyCredentialSource->backupStatus);
            $credential->setUvInitialized($publicKeyCredentialSource->uvInitialized);
        } else {
            // Need to convert TrustPath to array for storage
            $trustPath = $publicKeyCredentialSource->trustPath;
            $trustPathArray = [];
            if ($trustPath instanceof CertificateTrustPath) {
                $trustPathArray = ['x5c' => $trustPath->certificates];
            } elseif ($trustPath instanceof EmptyTrustPath) {
                $trustPathArray = [];
            } else {
                // Fallback or other types
                $trustPathArray = (array) $trustPath; 
            }

            $credential = new WebauthnCredential(
                $publicKeyCredentialSource->publicKeyCredentialId,
                $publicKeyCredentialSource->type,
                $publicKeyCredentialSource->transports,
                $publicKeyCredentialSource->attestationType,
                $trustPathArray,
                $publicKeyCredentialSource->aaguid,
                $publicKeyCredentialSource->credentialPublicKey,
                $publicKeyCredentialSource->userHandle,
                $publicKeyCredentialSource->counter
            );

            // Set optional fields
            $credential->setOtherUI($publicKeyCredentialSource->otherUI);
            $credential->setBackupEligible($publicKeyCredentialSource->backupEligible);
            $credential->setBackupStatus($publicKeyCredentialSource->backupStatus);
            $credential->setUvInitialized($publicKeyCredentialSource->uvInitialized);
            
            // Link to Employee
            // We need to find the Employee by userHandle (which is the ID)
            $employee = $entityManager->getRepository(Employee::class)->find($publicKeyCredentialSource->userHandle);
            if ($employee) {
                $credential->setEmployee($employee);
            }
        }

        $entityManager->persist($credential);
        $entityManager->flush();
    }
}
