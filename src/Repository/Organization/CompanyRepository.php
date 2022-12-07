<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Company;
use App\Entity\Platform\Entity;
use App\Entity\Platform\EntityPropertyGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CompanyRepository extends NestedTreeRepository
{
    private $em;
    public function __construct(ManagerRegistry $registry)
    {
      $entityClass = Company::class;
      $manager = $registry->getManagerForClass($entityClass);
      parent::__construct($manager, $manager->getClassMetadata($entityClass));
    }
}
