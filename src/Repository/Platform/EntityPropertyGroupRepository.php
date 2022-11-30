<?php

namespace App\Repository\Platform;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

use Doctrine\ORM\EntityManagerInterface;

class EntityPropertyGroupRepository extends NestedTreeRepository
{
    private $em;
    public function __construct()
    {
        $this->em = $this->getEntityManager();
    }
}
