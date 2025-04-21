<?php

namespace App\Component\Database\Bridge;

use App\Component\Database\Bridge\DoctrineDataSet;
use App\Component\Database\Bridge\FilterGroup;

class DataSource
{
    private DoctrineDataSet $dataSet;

    /**
     * DataSource constructor.
     *
     * @param DoctrineDataSet $dataSet
     */
    public function __construct(DoctrineDataSet $dataSet)
    {
        $this->dataSet = $dataSet;
    }

    /**
     * 设置过滤条件
     *
     * @param FilterGroup $filterGroup
     */
    public function setFilters(FilterGroup $filterGroup): void
    {
        $this->dataSet->setFilters($filterGroup);
    }

    /**
     * 设置分页偏移量
     *
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        $this->dataSet->setOffset($offset);
    }

    /**
     * 设置分页每页限制
     *
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->dataSet->setLimit($limit);
    }

    /**
     * 设置排序
     *
     * @param string $field
     * @param string $direction
     */
    public function setSort(string $field, string $direction): void
    {
        $this->dataSet->setSort($field, $direction);
    }

    /**
     * 获取数据
     *
     * @return iterable
     */
    public function getData(): iterable
    {
        return $this->dataSet->getData();
    }

    /**
     * 获取数据总数
     *
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->dataSet->getTotalCount();
    }
}
