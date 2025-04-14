<?php

namespace App\Repository\Organization;

use App\Entity\Organization\PositionLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PositionLevel|null find($id, $lockMode = null, $lockVersion = null)
 * @method PositionLevel|null findOneBy(array $criteria, array $orderBy = null)
 * @method PositionLevel[]    findAll()
 * @method PositionLevel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PositionLevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PositionLevel::class);
    }

    /**
     * 查找所有启用的职务级别，按级别序号排序
     */
    public function findAllActive()
    {
        return $this->createQueryBuilder('pl')
            ->andWhere('pl.state = :state')
            ->setParameter('state', true)
            ->orderBy('pl.levelOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}