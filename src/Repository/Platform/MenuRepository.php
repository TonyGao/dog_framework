<?php

namespace App\Repository\Platform;

use App\Entity\Platform\Menu;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class MenuRepository extends NestedTreeRepository
{
  private $em;
  public function __construct(ManagerRegistry $registry)
  {
    $entityClass = Menu::class;
    $em = $registry->getManagerForClass($entityClass);
    parent::__construct($em, $em->getClassMetadata($entityClass));
  }
}
