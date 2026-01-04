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
        // 克隆 QueryBuilder 以避免影响原始查询
        $countQueryBuilder = clone $this->queryBuilder;
        $countQueryBuilder->select('COUNT(e.id)'); // 假设每个实体有id字段
        
        // 应用过滤条件但不应用分页和排序
        if ($this->filters) {
            $this->applyFiltersToQueryBuilder($countQueryBuilder, $this->filters);
        }
        
        try {
            $result = $countQueryBuilder->getQuery()->getSingleScalarResult();
            return (int) $result;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return 0;
        }
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
        if ($this->filters) {
            $this->applyFiltersToQueryBuilder($this->queryBuilder, $this->filters);
        }
    }

    /**
     * 应用过滤条件到指定的 QueryBuilder
     *
     * @param QueryBuilder $queryBuilder
     * @param FilterGroup $filters
     */
    private function applyFiltersToQueryBuilder(QueryBuilder $queryBuilder, FilterGroup $filters): void
    {
        $expression = $this->buildExpression($queryBuilder, $filters);
        if ($expression) {
            $queryBuilder->andWhere($expression);
        }
    }

    private function buildExpression(QueryBuilder $queryBuilder, FilterGroup $group)
    {
        $expr = $queryBuilder->expr();
        $logic = strtoupper($group->getLogic());
        $conditions = $group->getConditions();
        
        if (empty($conditions)) {
            return null;
        }

        $predicates = [];
        foreach ($conditions as $index => $condition) {
            if ($condition instanceof FilterGroup) {
                $nestedExpr = $this->buildExpression($queryBuilder, $condition);
                if ($nestedExpr) {
                    $predicates[] = $nestedExpr;
                }
            } elseif ($condition instanceof FilterCondition) {
                $parameterName = 'filter_' . uniqid();
                $predicate = $this->createPredicate($queryBuilder, $condition, $parameterName);
                if ($predicate) {
                    $predicates[] = $predicate;
                }
            }
        }

        if (empty($predicates)) {
            return null;
        }

        if ($logic === 'OR') {
            return $expr->orX(...$predicates);
        } else {
            return $expr->andX(...$predicates);
        }
    }

    private function createPredicate(QueryBuilder $queryBuilder, FilterCondition $condition, string $parameterName)
    {
        $fieldName = $condition->getField();
        $rootAlias = $queryBuilder->getRootAliases()[0];
        
        // Handle Dot Notation for Relations (e.g. department.name)
        if (str_contains($fieldName, '.')) {
            $parts = explode('.', $fieldName);
            $currentAlias = $rootAlias;
            
            // Iterate through relations (all parts except the last one)
            for ($i = 0; $i < count($parts) - 1; $i++) {
                $relation = $parts[$i];
                $nextAlias = $currentAlias . '_' . $relation; // Unique alias based on path
                
                // Check if join already exists
                $joinExists = false;
                $joins = $queryBuilder->getDQLPart('join');
                
                // Joins are grouped by the alias they are joined to (or root alias)
                foreach ($joins as $root => $joinList) {
                    foreach ($joinList as $join) {
                        /** @var \Doctrine\ORM\Query\Expr\Join $join */
                        if ($join->getAlias() === $nextAlias) {
                            $joinExists = true;
                            break 2;
                        }
                    }
                }
                
                if (!$joinExists) {
                    $queryBuilder->leftJoin($currentAlias . '.' . $relation, $nextAlias);
                }
                $currentAlias = $nextAlias;
            }
            
            $field = $currentAlias . '.' . end($parts);
        } else {
            $field = $rootAlias . '.' . $fieldName;
        }

        $operator = $condition->getOperator();
        $value = $condition->getValue();
        $expr = $queryBuilder->expr();

        switch ($operator) {
            case '=':
            case 'equals':
                $queryBuilder->setParameter($parameterName, $value);
                return $expr->eq($field, ':' . $parameterName);
            case '!=':
            case 'not_equals':
                $queryBuilder->setParameter($parameterName, $value);
                return $expr->neq($field, ':' . $parameterName);
            case '>':
            case 'greater_than':
                $queryBuilder->setParameter($parameterName, $value);
                return $expr->gt($field, ':' . $parameterName);
            case '<':
            case 'less_than':
                $queryBuilder->setParameter($parameterName, $value);
                return $expr->lt($field, ':' . $parameterName);
            case '>=':
            case 'greater_than_or_equal':
                $queryBuilder->setParameter($parameterName, $value);
                return $expr->gte($field, ':' . $parameterName);
            case '<=':
            case 'less_than_or_equal':
                $queryBuilder->setParameter($parameterName, $value);
                return $expr->lte($field, ':' . $parameterName);
            case 'LIKE':
                $queryBuilder->setParameter($parameterName, $value);
                return $expr->like($field, ':' . $parameterName);
            case 'contains':
                $queryBuilder->setParameter($parameterName, '%' . $value . '%');
                return $expr->like($field, ':' . $parameterName);
            case 'not_contains':
                $queryBuilder->setParameter($parameterName, '%' . $value . '%');
                return $expr->notLike($field, ':' . $parameterName);
            case 'begins_with':
                $queryBuilder->setParameter($parameterName, $value . '%');
                return $expr->like($field, ':' . $parameterName);
            case 'ends_with':
                $queryBuilder->setParameter($parameterName, '%' . $value);
                return $expr->like($field, ':' . $parameterName);
            case 'IN':
            case 'in':
                $queryBuilder->setParameter($parameterName, $value);
                return $expr->in($field, ':' . $parameterName);
            case 'is_null':
                return $expr->isNull($field);
            case 'is_not_null':
                return $expr->isNotNull($field);
            default:
                throw new \InvalidArgumentException("Unsupported operator: " . $operator);
        }
    }
}
