<?php

namespace App\Component\Database\Bridge;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use App\Component\Database\Bridge\FilterCondition;
use App\Component\Database\Bridge\FilterGroup;

class DoctrineDataSet implements DataSetInterface
{
    private EntityManagerInterface $entityManager;
    private string $entityClass;
    private QueryBuilder $queryBuilder;

    private int $offset = 0;
    private int $limit = 10;
    private ?FilterGroup $filters = null;
    private ?string $sortField = null;
    private ?string $sortDirection = null;

    /**
     * DoctrineDataSet constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param string $entityClass
     */
    public function __construct(EntityManagerInterface $entityManager, string $entityClass)
    {
        $this->entityManager = $entityManager;
        $this->entityClass = $entityClass;
        $this->queryBuilder = $entityManager->getRepository($entityClass)->createQueryBuilder('e');
    }

    /**
     * 获取所有数据
     *
     * @return iterable
     */
    public function getData(): iterable
    {
        $query = $this->buildQuery();
        return $query->getResult();
    }

    /**
     * 获取数据的总记录数
     *
     * @return int
     */
    public function getTotalCount(): int
    {
        $query = $this->buildQuery();
        $this->queryBuilder->select('COUNT(e.id)'); // 假设每个实体有id字段
        return (int) $query->getSingleScalarResult();
    }

    /**
     * 设置分页偏移量
     *
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * 设置分页每页限制
     *
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * 设置排序条件
     *
     * @param string $field
     * @param string $direction ASC 或 DESC
     */
    public function setSort(string $field, string $direction): void
    {
        $this->sortField = $field;
        $this->sortDirection = strtoupper($direction);
    }

    /**
     * 设置筛选条件
     *
     * @param FilterGroup $filters
     */
    public function setFilters(FilterGroup $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * 构建并返回查询
     *
     * @return Query
     */
    private function buildQuery(): Query
    {
        if ($this->filters) {
            $this->applyFilters();
        }

        if ($this->sortField && $this->sortDirection) {
            $this->queryBuilder->orderBy('e.' . $this->sortField, $this->sortDirection);
        }

        if ($this->limit > 0) {
            $this->queryBuilder->setMaxResults($this->limit);
        }

        if ($this->offset > 0) {
            $this->queryBuilder->setFirstResult($this->offset);
        }

        return $this->queryBuilder->getQuery();
    }

    /**
     * 应用过滤条件
     */
    private function applyFilters(): void
    {
        $conditions = $this->filters->getConditions();
        foreach ($conditions as $index => $condition) {
            $parameterName = 'filter_' . $index;
            $field = 'e.' . $condition->getField();
            $operator = $condition->getOperator();
            $value = $condition->getValue();

            switch ($operator) {
                case '=':
                    $this->queryBuilder->andWhere($field . ' = :' . $parameterName);
                    break;
                case '>':
                    $this->queryBuilder->andWhere($field . ' > :' . $parameterName);
                    break;
                case '<':
                    $this->queryBuilder->andWhere($field . ' < :' . $parameterName);
                    break;
                case 'LIKE':
                    $this->queryBuilder->andWhere($field . ' LIKE :' . $parameterName);
                    break;
                case 'IN':
                    $this->queryBuilder->andWhere($field . ' IN (:' . $parameterName . ')');
                    break;
                // 可以根据需求扩展更多操作符
                default:
                    throw new \InvalidArgumentException("Unsupported operator: " . $operator);
            }

            $this->queryBuilder->setParameter($parameterName, $value);
        }
    }
}
