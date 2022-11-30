<?php
namespace App\Annotation;

/**
 * @Annotation
 */
class Ef
{
    public $value = [];

    public function __construct(array $data)
    {
        $this->value['group'] = $data['group'] ?? null;
        $this->value['bf'] = $data['isBF'] ?? false;
    }

    public function getValue()
    {
        return $this->value;
    }
}
