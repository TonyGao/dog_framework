<?php

namespace App\Repository\Security;

use App\Entity\Security\PasswordPolicy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordPolicy>
 *
 * @method PasswordPolicy|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordPolicy|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordPolicy[]    findAll()
 * @method PasswordPolicy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasswordPolicyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordPolicy::class);
    }
}
