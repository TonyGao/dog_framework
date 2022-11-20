<?php

namespace App\Repository\Platform;

use App\Entity\Platform\OptionValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OptionValueRepository extends ServiceEntityRepository
{
  private $em;
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construc($registry, OptionValue::class);
    $this->em = $this->getEntityManager();
  }
}