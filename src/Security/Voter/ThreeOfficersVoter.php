<?php

namespace App\Security\Voter;

use App\Entity\System\SystemUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ThreeOfficersVoter extends Voter
{
    public const SYSTEM_CONFIG = 'SYSTEM_CONFIG';
    public const USER_MANAGE = 'USER_MANAGE';
    public const AUDIT_VIEW = 'AUDIT_VIEW';
    public const BUSINESS_DATA = 'BUSINESS_DATA';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::SYSTEM_CONFIG,
            self::USER_MANAGE,
            self::AUDIT_VIEW,
            self::BUSINESS_DATA,
        ]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof SystemUser) {
            // Employees might have access to BUSINESS_DATA, but not System/Audit stuff usually.
            // For now, let's say Employees can access BUSINESS_DATA.
            if ($attribute === self::BUSINESS_DATA) {
                return true;
            }
            return false;
        }

        $roles = $user->getRoles();

        switch ($attribute) {
            case self::SYSTEM_CONFIG:
                // Only SysAdmin
                return in_array('ROLE_SYS_ADMIN', $roles);
            
            case self::USER_MANAGE:
                // Only SecAdmin
                return in_array('ROLE_SEC_ADMIN', $roles);

            case self::AUDIT_VIEW:
                // Only Auditor
                return in_array('ROLE_AUDITOR', $roles);

            case self::BUSINESS_DATA:
                // SysAdmin, SecAdmin, Auditor should NOT access business data (strictly speaking)
                // But for now, let's enforce the "Three Officers" rule:
                // SysAdmin: No
                // SecAdmin: No
                // Auditor: Maybe read-only? Plan says "Auditor: View system logs". Not business data?
                // Plan says: SysAdmin prohibited "View business data". SecAdmin prohibited "Access business sensitive data".
                // So all 3 should return false for BUSINESS_DATA.
                return false;
        }

        return false;
    }
}
