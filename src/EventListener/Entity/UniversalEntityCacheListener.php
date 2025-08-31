<?php

namespace App\EventListener\Entity;

use App\Service\Platform\DataGridService;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * 通用实体缓存监听器
 * 监听配置的实体生命周期事件，自动清除相关缓存
 * 通过 services.yaml 配置需要监听的实体类列表
 */
class UniversalEntityCacheListener
{
    private DataGridService $dataGridService;
    private array $watchedEntities;

    public function __construct(DataGridService $dataGridService, array $watchedEntities = [])
    {
        $this->dataGridService = $dataGridService;
        $this->watchedEntities = $watchedEntities;
    }

    /**
     * 实体创建后处理
     */
    public function postPersist(LifecycleEventArgs $event): void
    {
        $this->handleEntityChange($event);
    }

    /**
     * 实体更新后处理
     */
    public function postUpdate(LifecycleEventArgs $event): void
    {
        $this->handleEntityChange($event);
    }

    /**
     * 实体删除后处理
     */
    public function postRemove(LifecycleEventArgs $event): void
    {
        $this->handleEntityChange($event);
    }

    /**
     * 处理实体变化
     */
    private function handleEntityChange(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        $entityClass = get_class($entity);
        
        // 检查是否是需要监听的实体
        if (in_array($entityClass, $this->watchedEntities)) {
            $this->dataGridService->clearEntityCache($entityClass);
        }
    }

    /**
     * 添加需要监听的实体类
     */
    public function addWatchedEntity(string $entityClass): void
    {
        if (!in_array($entityClass, $this->watchedEntities)) {
            $this->watchedEntities[] = $entityClass;
        }
    }

    /**
     * 移除监听的实体类
     */
    public function removeWatchedEntity(string $entityClass): void
    {
        $key = array_search($entityClass, $this->watchedEntities);
        if ($key !== false) {
            unset($this->watchedEntities[$key]);
            $this->watchedEntities = array_values($this->watchedEntities);
        }
    }

    /**
     * 获取当前监听的实体类列表
     */
    public function getWatchedEntities(): array
    {
        return $this->watchedEntities;
    }
}