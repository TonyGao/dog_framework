<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Department;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class DepartmentRepository extends NestedTreeRepository
{
    public function __construct(ManagerRegistry $registry)
    {
      $entityClass = Department::class;
      $manager = $registry->getManagerForClass($entityClass);
      parent::__construct($manager, $manager->getClassMetadata($entityClass));
    }
}
