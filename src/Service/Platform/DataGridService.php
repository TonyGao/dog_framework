<?php

namespace App\Service\Platform;

use App\Entity\Platform\DataSource;
use App\Entity\Platform\DataGrid;
use App\Component\Database\Bridge\DataSource as BridgeDataSource;
use App\Component\Database\Bridge\DoctrineDataSet;
use App\Component\Database\View\DataGrid as ViewDataGrid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Psr\Cache\CacheItemPoolInterface;
use App\Service\Platform\CacheConfig;

class DataGridService
{
    private EntityManagerInterface $entityManager;
    private CacheItemPoolInterface $cache;
    
    // 缓存过期时间（秒）
    private const CACHE_TTL = 3600; // 1小时
    
    // 缓存键前缀
    private const CACHE_PREFIX = 'datagrid_';

    public function __construct(EntityManagerInterface $entityManager, CacheItemPoolInterface $cache)
    {
        $this->entityManager = $entityManager;
        $this->cache = $cache;
    }

    /**
     * 根据实体类名查找对应的 DataGrid 配置
     *
     * @param string $entityClass
     * @return DataGrid|null
     */
    public function findDataGridByEntityClass(string $entityClass): ?DataGrid
    {
        $dataSourceRepo = $this->entityManager->getRepository(DataSource::class);
        $dataSource = $dataSourceRepo->findOneBy([
            'resource' => $entityClass,
            'type' => 'entity'
        ]);

        if (!$dataSource) {
            return null;
        }

        $dataGridRepo = $this->entityManager->getRepository(DataGrid::class);
        return $dataGridRepo->findOneBy(['dataSource' => $dataSource]);
    }

    /**
     * 创建 ViewDataGrid 实例
     *
     * @param string $entityClass
     * @param int $page
     * @param int $pageSize
     * @return ViewDataGrid|null
     */
    public function createViewDataGrid(string $entityClass, int $page = 1, int $pageSize = 20): ?ViewDataGrid
    {
        $dataGrid = $this->findDataGridByEntityClass($entityClass);
        if (!$dataGrid) {
            return null;
        }

        $dataSource = $dataGrid->getDataSource();
        if (!$dataSource || $dataSource->getType() !== 'entity') {
            return null;
        }

        // 创建 DoctrineDataSet
        $doctrineDataSet = new DoctrineDataSet($this->entityManager, $dataSource->getResource());
        
        // 设置分页
        $offset = ($page - 1) * $pageSize;
        $doctrineDataSet->setOffset($offset);
        $doctrineDataSet->setLimit($pageSize);

        // 创建 Bridge DataSource
        $bridgeDataSource = new BridgeDataSource($doctrineDataSet);

        // 创建 ViewDataGrid
        $viewDataGrid = new ViewDataGrid($bridgeDataSource);

        // 从配置中添加列
        $config = $dataGrid->getDefaultConfigData();
        if ($config && isset($config['columns'])) {
            foreach ($config['columns'] as $column) {
                if (isset($column['label']) && isset($column['field'])) {
                    $viewDataGrid->addColumn($column['label'], $column['field']);
                }
            }
        }

        // 设置默认排序
        if ($config && isset($config['defaultSort'])) {
            $sortConfig = $config['defaultSort'];
            if (isset($sortConfig['field']) && isset($sortConfig['direction'])) {
                $viewDataGrid->setSort($sortConfig['field'], $sortConfig['direction']);
            }
        }

        return $viewDataGrid;
    }

    /**
     * 获取表格数据和配置
     *
     * @param string $entityClass 实体类名
     * @param int $page 页码
     * @param int $pageSize 每页大小
     * @param CacheConfig|string|null $cacheConfig 缓存配置，支持CacheConfig对象或语义化字符串
     * @return array
     */
    public function getTableData(string $entityClass, int $page = 1, int $pageSize = 20, $cacheConfig = null): array
    {
        // 如果传入的是字符串，转换为CacheConfig对象
        if (is_string($cacheConfig)) {
            $cacheConfig = CacheConfig::fromString($cacheConfig);
        }
        
        if ($cacheConfig && $cacheConfig->isEnabled()) {
            return $this->getCachedTableData($entityClass, $page, $pageSize, $cacheConfig->getTtl());
        }
        
        return $this->getTableDataInternal($entityClass, $page, $pageSize);
    }
    
    /**
     * 获取缓存的表格数据
     *
     * @param string $entityClass
     * @param int $page
     * @param int $pageSize
     * @param int|null $cacheTtl 缓存时间（秒），null使用默认值
     * @return array
     */
    private function getCachedTableData(string $entityClass, int $page, int $pageSize, ?int $cacheTtl = null): array
    {
        $cacheKey = $this->generateCacheKey($entityClass, $page, $pageSize);
        
        // 先尝试获取缓存
        $cacheItem = $this->cache->getItem($cacheKey);
        
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }
        
        // 缓存未命中，获取数据并缓存
        $data = $this->getTableDataInternal($entityClass, $page, $pageSize);
        
        $cacheItem->set($data);
        $cacheItem->expiresAfter($cacheTtl ?? self::CACHE_TTL);
        
        // 注意：当前缓存池不支持标签功能
        
        $this->cache->save($cacheItem);
        
        return $data;
    }
    
    /**
     * 内部获取表格数据的方法
     *
     * @param string $entityClass
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    private function getTableDataInternal(string $entityClass, int $page = 1, int $pageSize = 20): array
    {
        $viewDataGrid = $this->createViewDataGrid($entityClass, $page, $pageSize);
        
        if (!$viewDataGrid) {
            return [
                'data' => [],
                'totalItems' => 0,
                'totalPages' => 0,
                'currentPage' => $page,
                'pageSize' => $pageSize,
                'gridConfig' => null
            ];
        }

        // 先获取总数，再获取数据，避免 QueryBuilder 被修改
        $totalItems = $viewDataGrid->getTotalCount();
        $data = $viewDataGrid->getData();
        $dataGrid = $this->findDataGridByEntityClass($entityClass);
        
        // 转换数据为数组格式
        $tableData = [];
        foreach ($data as $item) {
            $row = [];
            $config = $dataGrid->getDefaultConfigData();
            if ($config && isset($config['columns'])) {
                foreach ($config['columns'] as $column) {
                    if (isset($column['field'])) {
                        $field = $column['field'];
                        $value = $this->getFieldValue($item, $field, $column);
                        $row[$field] = $value;
                    }
                }
            }
            $tableData[] = $row;
        }

        return [
            'data' => $tableData,
            'totalItems' => $totalItems,
            'totalPages' => ceil($totalItems / $pageSize),
            'currentPage' => $page,
            'pageSize' => $pageSize,
            'gridConfig' => $dataGrid ? $dataGrid->getDefaultConfigData() : null
        ];
    }

    /**
     * 获取字段值
     *
     * @param object $item
     * @param string $field
     * @param array $column
     * @return mixed
     */
    private function getFieldValue(object $item, string $field, array $column)
    {
        $getter = 'get' . ucfirst($field);
        if (!method_exists($item, $getter)) {
            return null;
        }

        $value = $item->$getter();

        // 根据列配置处理不同类型的值
        if (isset($column['type'])) {
            switch ($column['type']) {
                case 'relation':
                    if (is_object($value)) {
                        $displayField = $column['displayField'] ?? 'name';
                        $displayGetter = 'get' . ucfirst($displayField);
                        if (method_exists($value, $displayGetter)) {
                            return $value->$displayGetter();
                        }
                    }
                    return $value ? (string) $value : '';
                    
                case 'boolean':
                    if (is_bool($value)) {
                        $trueText = $column['trueText'] ?? '是';
                        $falseText = $column['falseText'] ?? '否';
                        return $value ? $trueText : $falseText;
                    }
                    return $value;
                    
                case 'datetime':
                    if ($value instanceof \DateTimeInterface) {
                        return $value->format('Y-m-d H:i:s');
                    }
                    return $value;
                    
                case 'actions':
                    // 操作列返回 ID 用于前端生成操作按钮
                    return $item->getId();
                    
                default:
                    return $value;
            }
        }

        // 默认处理逻辑
        if (is_object($value) && method_exists($value, 'getName')) {
            return $value->getName();
        } elseif (is_bool($value)) {
            return $value ? '启用' : '停用';
        }

        return $value;
    }
    
    /**
     * 生成缓存键
     *
     * @param string $entityClass
     * @param int $page
     * @param int $pageSize
     * @return string
     */
    private function generateCacheKey(string $entityClass, int $page, int $pageSize): string
    {
        // 使用类名的最后一部分和哈希来缩短键名
        $className = substr(strrchr($entityClass, '\\'), 1);
        $hash = substr(md5($entityClass), 0, 8);
        return self::CACHE_PREFIX . $className . '_' . $hash . '_p' . $page . '_s' . $pageSize;
    }
    
    /**
     * 清除指定实体的缓存
     *
     * @param string $entityClass
     * @return void
     */
    public function clearEntityCache(string $entityClass): void
    {
        // 生成该实体类的缓存键前缀
        $className = substr(strrchr($entityClass, '\\'), 1);
        $hash = substr(md5($entityClass), 0, 8);
        $keyPrefix = self::CACHE_PREFIX . $className . '_' . $hash;
        
        // 尝试使用反射获取缓存池的内部存储
        try {
            $reflection = new \ReflectionClass($this->cache);
            if ($reflection->hasProperty('values')) {
                $valuesProperty = $reflection->getProperty('values');
                $valuesProperty->setAccessible(true);
                $values = $valuesProperty->getValue($this->cache);
                
                $deletedCount = 0;
                foreach (array_keys($values) as $key) {
                    if (strpos($key, $keyPrefix) === 0) {
                        $this->cache->deleteItem($key);
                        $deletedCount++;
                    }
                }
            } else {
                // 如果无法访问内部存储，清除所有缓存
                $this->cache->clear();
            }
        } catch (\Exception $e) {
            // 如果精确清除失败，回退到清除所有缓存
            $this->cache->clear();
        }
    }
    
    /**
     * 清除所有 DataGrid 缓存
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        $this->cache->clear();
    }
}