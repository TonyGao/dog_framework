<?php

namespace App\Lib;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Form\Common\SwitchType;

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
        }

        return $class;
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
