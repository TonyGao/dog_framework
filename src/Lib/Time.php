<?php

namespace App\Lib;

class Time
{
    public static function curMicroSec()
    {
        // 获取当前时间戳，包含微秒
        $microtime = microtime(true);

        // 分别获取秒部分和微秒部分
        $seconds = floor($microtime);
        $microseconds = sprintf("%06d", ($microtime - $seconds) * 1000000);

        // 转换为日期时间格式
        $datetime = date("YmdHis", $seconds);

        // 添加微秒到秒后面
        return $datetime . $microseconds;
    }
}
