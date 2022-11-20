<?php

namespace App\Repository\Platform;

use App\Entity\Platform\Options;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OptionsRepository extends ServiceEntityRepository
{
  private $em;
  public function __construct(ManagerRegistry $registry)
  {
    parent::__contruct($registry, Options::class);
    $this->em = $this->getEntityManager();
  }
}