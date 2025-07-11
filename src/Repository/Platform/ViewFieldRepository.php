<?php

namespace App\Repository\Platform;

use App\Entity\Platform\ViewField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ViewField Repository
 * 视图字段仓库类，用于管理视图字段的数据库操作
 */
class ViewFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ViewField::class);
    }

    /**
     * 根据视图ID获取所有字段
     *
     * @param string $viewId 视图ID
     * @return ViewField[]
     */
    public function findByViewId(string $viewId): array
    {
        return $this->createQueryBuilder('vf')
            ->andWhere('vf.view = :viewId')
            ->setParameter('viewId', $viewId)
            ->orderBy('vf.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据视图ID和实体ID获取字段
     *
     * @param string $viewId 视图ID
     * @param string $entityId 实体ID
     * @return ViewField[]
     */
    public function findByViewAndEntity(string $viewId, string $entityId): array
    {
        return $this->createQueryBuilder('vf')
            ->andWhere('vf.view = :viewId')
            ->andWhere('vf.entity = :entityId')
            ->setParameter('viewId', $viewId)
            ->setParameter('entityId', $entityId)
            ->orderBy('vf.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取已插入标签的字段
     *
     * @param string $viewId 视图ID
     * @return ViewField[]
     */
    public function findInsertedLabels(string $viewId): array
    {
        return $this->createQueryBuilder('vf')
            ->andWhere('vf.view = :viewId')
            ->andWhere('vf.isLabelInserted = :inserted')
            ->setParameter('viewId', $viewId)
            ->setParameter('inserted', true)
            ->orderBy('vf.labelInsertPosition', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取已插入值的字段
     *
     * @param string $viewId 视图ID
     * @return ViewField[]
     */
    public function findInsertedValues(string $viewId): array
    {
        return $this->createQueryBuilder('vf')
            ->andWhere('vf.view = :viewId')
            ->andWhere('vf.isValueInserted = :inserted')
            ->setParameter('viewId', $viewId)
            ->setParameter('inserted', true)
            ->orderBy('vf.valueInsertPosition', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 检查字段是否已存在
     *
     * @param string $viewId 视图ID
     * @param string $entityId 实体ID
     * @param string $fieldName 字段名称
     * @return bool
     */
    public function fieldExists(string $viewId, string $entityId, string $fieldName): bool
    {
        $count = $this->createQueryBuilder('vf')
            ->select('COUNT(vf.id)')
            ->andWhere('vf.view = :viewId')
            ->andWhere('vf.entity = :entityId')
            ->andWhere('vf.fieldName = :fieldName')
            ->setParameter('viewId', $viewId)
            ->setParameter('entityId', $entityId)
            ->setParameter('fieldName', $fieldName)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * 获取下一个排序号
     *
     * @param string $viewId 视图ID
     * @return int
     */
    public function getNextSortOrder(string $viewId): int
    {
        $maxOrder = $this->createQueryBuilder('vf')
            ->select('MAX(vf.sortOrder)')
            ->andWhere('vf.view = :viewId')
            ->setParameter('viewId', $viewId)
            ->getQuery()
            ->getSingleScalarResult();

        return ($maxOrder ?? 0) + 1;
    }

    /**
     * 批量删除视图的所有字段
     *
     * @param string $viewId 视图ID
     * @return int 删除的记录数
     */
    public function deleteByViewId(string $viewId): int
    {
        return $this->createQueryBuilder('vf')
            ->delete()
            ->andWhere('vf.view = :viewId')
            ->setParameter('viewId', $viewId)
            ->getQuery()
            ->execute();
    }
}