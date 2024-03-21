<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;

class TwigExtension extends AbstractExtension
{
    public function getTests() {
        return array(
            new TwigTest('instanceof', array($this, 'isInstanceOf')),
         );
     }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getObjectFields', [$this, 'getObjectFields']),
            new TwigFunction('getStdClassFields', [$this, 'getStdClassFields']),
            new TwigFunction('instanceof', [$this, 'isInstanceof']),
        ];
    }

    public function getObjectFields($object): array
    {
        // 获取对象的所有属性
        $reflection = new \ReflectionClass($object);

        $properties = $reflection->getProperties();
        $fields = [];

        // 获取属性名（字段名）
        foreach ($properties as $property) {
            $fields[] = $property->getName();
        }

        return $fields;
    }

    public function getStdClassFields($object): array
    {
        // 获取对象的所有属性
        $properties = get_object_vars($object);
        $fields = [];

        // 获取属性名（字段名）
        foreach ($properties as $key => $value) {
            $fields[] = $key;
        }

        return $fields;
    }

    public function isInstanceof($object, $class)
    {
        if (is_object($object)) {
            $reflectionClass = new \ReflectionClass($class);
            return $reflectionClass->isInstance($object);
        } else {
            return false;
        }
        
    }
}
