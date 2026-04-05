<?php

namespace App\Service\System;

use App\Entity\System\AuditLog;
use App\Entity\Organization\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class AuditService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private RequestStack $requestStack
    ) {}

    public function log(string $action, string $target, array $details = [], ?UserInterface $explicitUser = null): void
    {
        $log = new AuditLog();
        $log->setAction($action);
        $log->setTarget($target);
        $log->setDetails($details);

        $user = $explicitUser ?? $this->security->getUser();
        if ($user) {
            if ($user instanceof Employee) {
                if ($user->getIsSystem()) {
                    $log->setOperatorType('system_user');
                } else {
                    $log->setOperatorType('employee');
                }
                
                // Using reflection or assuming getId exists if not visible, but it should be there.
                // If getId() is not available, we might need another way, but let's assume standard entity.
                if (method_exists($user, 'getId')) {
                    $id = $user->getId();
                    $log->setOperatorId($id instanceof Uuid ? $id->toRfc4122() : (string)$id);
                } else {
                    $log->setOperatorId($user->getUserIdentifier());
                }
            } else {
                $log->setOperatorType('unknown');
                $log->setOperatorId($user->getUserIdentifier());
            }
        } else {
            $log->setOperatorType('system');
            $log->setOperatorId('system');
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $log->setIpAddress($request->getClientIp());
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
