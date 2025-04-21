<?php

namespace App\Component\Database\Bridge;

/**
 * FilterCondition 类表示单个过滤条件。
 */
class FilterCondition
{
    private string $field;
    private string $operator;
    private mixed $value;

    /**
     * FilterCondition 构造函数
     *
     * @param string $field 字段名
     * @param string $operator 操作符，如 '=', '>', '<', 'like', 'in', 等
     * @param mixed $value 值
     */
    public function __construct(string $field, string $operator, mixed $value)
    {
        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
