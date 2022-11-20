<?php

namespace App\Repository\Platform;

use App\Entity\Platform\Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EntityRepository extends ServiceEntityRepository
{
    private $em;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entity::class);
        $this->em = $this->getEntityManager();
    }
}
