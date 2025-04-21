<?php

namespace App\Component\Database\Bridge;

/**
 * Interface DataSetInterface
 * 用于抽象不同类型的数据集，例如 Doctrine 实体集、API 返回数据、数组等。
 */
interface DataSetInterface
{
    /**
     * 获取所有数据（可以为分页后的一页，也可以是全量）。
     *
     * @return iterable
     */
    public function getData(): iterable;

    /**
     * 获取数据的总记录数（未分页时的总数）。
     *
     * @return int
     */
    public function getTotalCount(): int;

    /**
     * 设置分页偏移量。
     *
     * @param int $offset
     */
    public function setOffset(int $offset): void;

    /**
     * 设置分页每页限制。
     *
     * @param int $limit
     */
    public function setLimit(int $limit): void;

    /**
     * 设置排序条件。
     *
     * @param string $field
     * @param string $direction ASC 或 DESC
     */
    public function setSort(string $field, string $direction): void;

    /**
     * 设置筛选条件。
     * 支持复杂的查询条件，允许使用操作符、逻辑组合等。
     *
     * @param FilterGroup $filters
     */
    public function setFilters(FilterGroup $filters): void;
}
