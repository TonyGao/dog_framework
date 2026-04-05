<?php

namespace App\Repository\Security;

use App\Entity\Security\PasswordStrengthRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordStrengthRule>
 *
 * @method PasswordStrengthRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordStrengthRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordStrengthRule[]    findAll()
 * @method PasswordStrengthRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasswordStrengthRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordStrengthRule::class);
    }
}
