<?php

namespace App\Repository\System;

use App\Entity\System\EmailConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailConfig>
 *
 * @method EmailConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailConfig[]    findAll()
 * @method EmailConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailConfig::class);
    }
}
