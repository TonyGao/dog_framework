<?php

namespace App\Repository\Organization;

use App\Entity\OrgUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
  private $em;
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, OrgUser::class);
    $this->em = $this->getEntityManager();
  }
}
