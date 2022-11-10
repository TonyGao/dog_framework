<?php

namespace App\Repository\Admin;

use App\Entity\Admin\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MenuRepository extends ServiceEntityRepository
{
  private $em;
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Menu::class);
    $this->em = $this->getEntityManager();
  }

  public function findByName(string $name): ?Menu
  {
    $qb = $this->em->createQueryBuilder();
    $qb->select('m')
      ->from('App\Entity\Admin\Menu', 'm')
      ->where('m.label = :name')
      ->setParameter('name', $name);
    $menu = $qb->getQuery()->getResult();

    return $menu;
  }
}