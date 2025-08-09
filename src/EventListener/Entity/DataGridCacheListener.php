<?php

namespace App\EventListener\Entity;

use App\Entity\Platform\DataGrid;
use App\Entity\Platform\DataSource;
use App\Service\Platform\DataGridService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * DataGrid 配置缓存监听器
 * 当 DataGrid 或 DataSource 配置发生变化时自动清除相关缓存
 */
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdateDataGrid', entity: DataGrid::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdateDataSource', entity: DataSource::class)]
class DataGridCacheListener
{
    private DataGridService $dataGridService;

    public function __construct(DataGridService $dataGridService)
    {
        $this->dataGridService = $dataGridService;
    }

    /**
     * DataGrid 配置更新后清除缓存
     */
    public function postUpdateDataGrid(DataGrid $dataGrid, LifecycleEventArgs $event): void
    {
        $dataSource = $dataGrid->getDataSource();
        if ($dataSource && $dataSource->getType() === 'entity') {
            $entityClass = $dataSource->getResource();
            $this->dataGridService->clearEntityCache($entityClass);
        }
    }

    /**
     * DataSource 配置更新后清除缓存
     */
    public function postUpdateDataSource(DataSource $dataSource, LifecycleEventArgs $event): void
    {
        if ($dataSource->getType() === 'entity') {
            $entityClass = $dataSource->getResource();
            // 清除指定实体的缓存
            $this->dataGridService->clearEntityCache($entityClass);
        }
    }
}