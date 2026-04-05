<?php

namespace App\Service\Platform;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * 用户在线状态追踪服务
 */
class PresenceService
{
    private CacheItemPoolInterface $cache;
    private const CACHE_PREFIX = 'presence_user_';
    private const ONLINE_TIMEOUT = 60; // 60 秒无心跳视为离线

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * 更新用户在线状态 (心跳)
     */
    public function heartbeat(string $userId): void
    {
        $item = $this->cache->getItem(self::CACHE_PREFIX . $userId);
        $item->set(time());
        $item->expiresAfter(self::ONLINE_TIMEOUT);
        $this->cache->save($item);
    }

    /**
     * 判断用户是否在线
     */
    public function isOnline(string $userId): bool
    {
        return $this->cache->hasItem(self::CACHE_PREFIX . $userId);
    }

    /**
     * 获取用户最后活跃时间
     */
    public function getLastSeen(string $userId): ?int
    {
        $item = $this->cache->getItem(self::CACHE_PREFIX . $userId);
        if (!$item->isHit()) {
            return null;
        }
        return $item->get();
    }

    /**
     * 手动标记用户离线
     */
    public function markOffline(string $userId): void
    {
        $this->cache->deleteItem(self::CACHE_PREFIX . $userId);
    }
}
