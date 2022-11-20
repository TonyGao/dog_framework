<?php

namespace App\Repository\Platform;

use App\Entity\Platform\EntityProperty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EntityPropertyRepository extends ServiceEntityRepository
{
    private $em;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityProperty::class);
        $this->em = $this->getEntityManager();
    }
}
