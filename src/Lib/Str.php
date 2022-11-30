<?php

namespace App\Lib;

class Str
{
    public static function getComment($text) {
        $comment = '';
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $text) as $line){
            if (substr($line, 0, 1) !== '@') {
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
