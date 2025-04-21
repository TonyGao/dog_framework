<?php

namespace App\Component\Database\View;

use App\Component\Database\Bridge\DataSource;
use App\Component\Database\Bridge\FilterGroup;
use App\Component\Database\Bridge\FilterCondition;

class DataGrid
{
    private DataSource $dataSource;
    private array $columns = [];
    private array $filters = [];
    private string $sortField = '';
    private string $sortDirection = 'ASC';

    /**
     * DataGrid constructor.
     *
     * @param DataSource $dataSource
     */
    public function __construct(DataSource $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * 添加表格列
     *
     * @param string $label
     * @param string $field
     * @return $this
     */
    public function addColumn(string $label, string $field): self
    {
        $this->columns[] = ['label' => $label, 'field' => $field];
        return $this;
    }

    /**
     * 设置过滤条件
     *
     * @param FilterGroup $filterGroup
     * @return $this
     */
    public function setFilters(FilterGroup $filterGroup): self
    {
        $this->dataSource->setFilters($filterGroup);
        return $this;
    }

    /**
     * 设置排序字段
     *
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function setSort(string $field, string $direction = 'ASC'): self
    {
        $this->sortField = $field;
        $this->sortDirection = $direction;
        $this->dataSource->setSort($field, $direction);
        return $this;
    }

    /**
     * 获取数据
     *
     * @return iterable
     */
    public function getData(): iterable
    {
        return $this->dataSource->getData();
    }

    /**
     * 获取数据总数
     *
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->dataSource->getTotalCount();
    }

    /**
     * 渲染表格
     *
     * @return array
     */
    public function render(): array
    {
        $data = $this->getData();
        $totalCount = $this->getTotalCount();
        $result = [];

        // 生成表头
        $headers = [];
        foreach ($this->columns as $column) {
            $headers[] = $column['label'];
        }

        $result['headers'] = $headers;

        // 生成数据行
        $rows = [];
        foreach ($data as $item) {
            $row = [];
            foreach ($this->columns as $column) {
                $row[] = $this->getValueForField($item, $column['field']);
            }
            $rows[] = $row;
        }

        $result['rows'] = $rows;
        $result['totalCount'] = $totalCount;

        return $result;
    }

    /**
     * 获取某一字段的值
     *
     * @param object $item
     * @param string $field
     * @return mixed
     */
    private function getValueForField(object $item, string $field)
    {
        $getter = 'get' . ucfirst($field);
        if (method_exists($item, $getter)) {
            return $item->$getter();
        }

        return null;
    }
}
