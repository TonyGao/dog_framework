<?php

namespace App\Component\Database\Bridge;

/**
 * FilterGroup 类表示一个过滤条件组，可以包含多个 FilterCondition，并支持 AND/OR 组合。
 */
class FilterGroup
{
    private array $conditions = [];
    private string $logic = 'AND'; // 支持 AND 或 OR

    /**
     * 构造函数
     *
     * @param string $logic 逻辑操作符（AND 或 OR）
     */
    public function __construct(string $logic = 'AND')
    {
        $this->logic = strtoupper($logic);
    }

    /**
     * 添加一个过滤条件
     *
     * @param FilterCondition $condition
     * @return $this
     */
    public function addCondition(FilterCondition $condition): self
    {
        $this->conditions[] = $condition;
        return $this;
    }

    /**
     * 获取所有过滤条件
     *
     * @return FilterCondition[]
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * 获取当前的逻辑操作符
     *
     * @return string
     */
    public function getLogic(): string
    {
        return $this->logic;
    }
}
