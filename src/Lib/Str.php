<?php

namespace App\Lib;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\Common\SwitchType;
use App\Form\Common\DepartmentType;

class Str
{
    public static function getComment($text) {
        $comment = '';
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $text) as $key=>$line) {
            if ($key === 0 && (substr($line, 0, 1) !== '@')) {
                $comment .= $line;
            }
        }

        return $comment;
    }

    /**
     * 通过判断有没有以isBusinessEntity开始的注释判断是不是业务实体
     */
    public static function isBusinessEntity($text) {
        $result = false;
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $text) as $line){
            if (substr($line, 0, 16) === 'isBusinessEntity') {
                $result = true;
            }
        }

        return $result;
    }

    public static function convertFormType($type) {
        switch ($type) {
            case 'boolean':
                $class = SwitchType::class;
                break;
            case 'string':
                $class = TextType::class;
                break;
            case 'text':
                $class = TextareaType::class;
                break;
            case 'integer':
                $class = IntegerType::class;
                break;
            case 'department':
                $class = DepartmentType::class;
                break;
            case 'entity':
                $class = EntityType::class;
                break;
        }

        return $class;
    }

    /**
     * 将 Entity 类型的实体属性从targetEntity字符串转化为特定的formType
     * targetEntity字符串类似 "App\Entity\Organization\Department"
     */
    public static function convertFormTypeFromTargetEntity($targetEntity) {
        switch ($targetEntity) {
            case 'App\Entity\Organization\Department':
                $formType = DepartmentType::class;
                break;
            default:
                $formType = EntityType::class;
                break;
        }

        return $formType;
    }

    // public static function getGroup($text) {
    //     $group = '';
    //     foreach(preg_split("/((\r?\n)|(\r\n?))/", $text) as $line){
    //         if (substr($line, 0, 1) === '@EfGroup') {
    //             $group .= $line;
    //         }
    //     }

    //     return $comment;
    // }
}
