<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Position;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Position|null find($id, $lockMode = null, $lockVersion = null)
 * @method Position|null findOneBy(array $criteria, array $orderBy = null)
 * @method Position[]    findAll()
 * @method Position[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Position::class);
    }

    /**
     * 根据部门ID查找岗位
     */
    public function findByDepartment($departmentId)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.department = :departmentId')
            ->setParameter('departmentId', $departmentId)
            ->orderBy('p.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找启用状态的岗位
     */
    public function findActivePositions()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.state = :state')
            ->setParameter('state', true)
            ->orderBy('p.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}