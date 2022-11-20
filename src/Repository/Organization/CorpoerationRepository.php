<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Corporation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CorporationRepository extends ServiceEntityRepository
{
  private $em;
  public function __construct(ManagerRegistry $registry)
  {
    parent::__contruct($registry, Corporation::class);
    $this->em = $this->getEntityManager();
  }
}
