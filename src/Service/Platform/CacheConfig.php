<?php

namespace App\Service\Platform;

/**
 * 缓存配置类
 * 提供语义化的缓存配置选项
 */
class CacheConfig
{
    // 缓存策略常量
    public const DISABLED = 'disabled';     // 不使用缓存
    public const CACHED = 'cached';         // 启用缓存
    
    // 时间单位常量
    public const MINUTE = 60;
    public const HOUR = 3600;
    public const DAY = 86400;
    
    private string $strategy;
    private int $ttl;
    
    /**
     * 构造函数
     *
     * @param string $strategy 缓存策略 (disabled|cached)
     * @param int|string $duration 缓存时长，可以是秒数或语义化字符串
     */
    public function __construct(string $strategy = self::DISABLED, $duration = self::HOUR)
    {
        $this->strategy = $strategy;
        $this->ttl = $this->parseDuration($duration);
    }
    
    /**
     * 从语义化字符串创建缓存配置
     * 支持格式如: 'cached 3 hours', 'disabled', '5 minutes'
     *
     * @param string $semanticString 语义化字符串
     * @return self
     */
    public static function fromString(string $semanticString): self
    {
        $semanticString = strtolower(trim($semanticString));
        
        // 检查是否包含 'cached' 关键字
        if (strpos($semanticString, 'cached') === 0) {
            // 提取时间部分，如 'cached 3 hours' -> '3 hours'
            $timeString = trim(substr($semanticString, 6)); // 去掉 'cached'
            if (empty($timeString)) {
                return new self(self::CACHED, self::HOUR); // 默认1小时
            }
            return new self(self::CACHED, $timeString);
        }
        
        // 检查是否是禁用缓存
        if ($semanticString === 'disabled' || $semanticString === 'no cache' || $semanticString === 'nocache') {
            return new self(self::DISABLED);
        }
        
        // 直接解析时间字符串，默认启用缓存
        return new self(self::CACHED, $semanticString);
    }
    
    /**
     * 创建禁用缓存的配置
     *
     * @return self
     */
    public static function disabled(): self
    {
        return new self(self::DISABLED);
    }
    
    /**
     * 创建启用缓存的配置
     *
     * @param int|string $duration 缓存时长
     * @return self
     */
    public static function cached($duration = self::HOUR): self
    {
        return new self(self::CACHED, $duration);
    }
    
    /**
     * 创建分钟级缓存
     *
     * @param int $minutes 分钟数
     * @return self
     */
    public static function minutes(int $minutes): self
    {
        return new self(self::CACHED, $minutes * self::MINUTE);
    }
    
    /**
     * 创建小时级缓存
     *
     * @param int $hours 小时数
     * @return self
     */
    public static function hours(int $hours): self
    {
        return new self(self::CACHED, $hours * self::HOUR);
    }
    
    /**
     * 创建天级缓存
     *
     * @param int $days 天数
     * @return self
     */
    public static function days(int $days): self
    {
        return new self(self::CACHED, $days * self::DAY);
    }
    
    /**
     * 解析时长参数
     * 支持多种格式:
     * - 整数秒数: 3600
     * - 语义化字符串: "3 hours", "30 minutes", "2 days"
     * - 缩写格式: "3h", "30m", "2d"
     * - 小数格式: "1.5 hours", "2.5 days"
     *
     * @param int|string $duration
     * @return int
     */
    private function parseDuration($duration): int
    {
        if (is_int($duration)) {
            return $duration;
        }
        
        if (is_string($duration)) {
            $duration = strtolower(trim($duration));
            
            // 支持小数格式，如 "1.5 hours", "2.5 days"
            if (preg_match('/(\d+(?:\.\d+)?)\s*(second|seconds|sec|s)/', $duration, $matches)) {
                return (int)(floatval($matches[1]));
            }
            
            if (preg_match('/(\d+(?:\.\d+)?)\s*(minute|minutes|min|m)/', $duration, $matches)) {
                return (int)(floatval($matches[1]) * self::MINUTE);
            }
            
            if (preg_match('/(\d+(?:\.\d+)?)\s*(hour|hours|hr|h)/', $duration, $matches)) {
                return (int)(floatval($matches[1]) * self::HOUR);
            }
            
            if (preg_match('/(\d+(?:\.\d+)?)\s*(day|days|d)/', $duration, $matches)) {
                return (int)(floatval($matches[1]) * self::DAY);
            }
            
            if (preg_match('/(\d+(?:\.\d+)?)\s*(week|weeks|w)/', $duration, $matches)) {
                return (int)(floatval($matches[1]) * self::DAY * 7);
            }
            
            // 特殊字符串
            switch ($duration) {
                case '1 second':
                case 'second':
                    return 1;
                case '1 minute':
                case 'minute':
                    return self::MINUTE;
                case '1 hour':
                case 'hour':
                    return self::HOUR;
                case '1 day':
                case 'day':
                    return self::DAY;
                case '1 week':
                case 'week':
                    return self::DAY * 7;
            }
        }
        
        // 默认返回1小时
        return self::HOUR;
    }
    
    /**
     * 是否启用缓存
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->strategy === self::CACHED;
    }
    
    /**
     * 获取缓存时长（秒）
     *
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }
    
    /**
     * 获取缓存策略
     *
     * @return string
     */
    public function getStrategy(): string
    {
        return $this->strategy;
    }
    
    /**
     * 转换为人类可读的字符串
     *
     * @return string
     */
    public function toHumanString(): string
    {
        if (!$this->isEnabled()) {
            return 'disabled';
        }
        
        $ttl = $this->getTtl();
        
        if ($ttl >= self::DAY && $ttl % self::DAY === 0) {
            $days = $ttl / self::DAY;
            return $days === 1 ? 'cached 1 day' : "cached {$days} days";
        }
        
        if ($ttl >= self::HOUR && $ttl % self::HOUR === 0) {
            $hours = $ttl / self::HOUR;
            return $hours === 1 ? 'cached 1 hour' : "cached {$hours} hours";
        }
        
        if ($ttl >= self::MINUTE && $ttl % self::MINUTE === 0) {
            $minutes = $ttl / self::MINUTE;
            return $minutes === 1 ? 'cached 1 minute' : "cached {$minutes} minutes";
        }
        
        return $ttl === 1 ? 'cached 1 second' : "cached {$ttl} seconds";
    }
}