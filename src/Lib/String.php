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
}
