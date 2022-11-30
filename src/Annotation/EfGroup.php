<?php
namespace App\Annotation;

/**
 * @Annotation
 */
class EfGroup
{
    public $value;

    public function __construct(array $data)
    {
        $this->value = $data['value'];
    }

    public function getValue()
    {
        return $this->value;
    }
}
