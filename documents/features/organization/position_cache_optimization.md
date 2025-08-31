# 岗位数据缓存优化方案

## 问题背景

在之前的实现中，岗位数据的缓存管理存在两个主要问题：

1. **手动调用容易遗漏**：需要在每个修改岗位数据的地方手动调用 `clearEntityCache()` 方法，开发者容易忘记添加这个调用，导致缓存不一致。

2. **全量清除效率低**：即使只更新一条岗位数据，也需要清除所有相关缓存，影响系统性能。

## 解决方案

### 1. 自动化缓存管理

通过 Doctrine 事件监听器实现自动化的缓存管理，无需手动调用缓存清除方法。

#### 实现文件：`src/EventListener/Entity/PositionCacheListener.php`

```php
<?php

namespace App\EventListener\Entity;

use App\Entity\Organization\Position;
use App\Service\Platform\DataGridService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Position 实体缓存监听器
 * 当 Position 实体发生变化时自动清除相关缓存
 */
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Position::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Position::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: Position::class)]
class PositionCacheListener
{
    private DataGridService $dataGridService;

    public function __construct(DataGridService $dataGridService)
    {
        $this->dataGridService = $dataGridService;
    }

    /**
     * Position 创建后清除缓存
     */
    public function postPersist(Position $position, LifecycleEventArgs $event): void
    {
        $this->clearPositionCache();
    }

    /**
     * Position 更新后清除缓存
     */
    public function postUpdate(Position $position, LifecycleEventArgs $event): void
    {
        $this->clearPositionCache();
    }

    /**
     * Position 删除后清除缓存
     */
    public function postRemove(Position $position, LifecycleEventArgs $event): void
    {
        $this->clearPositionCache();
    }

    /**
     * 清除 Position 相关缓存
     */
    private function clearPositionCache(): void
    {
        $this->dataGridService->clearEntityCache(Position::class);
    }
}
```

### 2. 精确缓存清除

改进 `DataGridService` 的 `clearEntityCache` 方法，实现按实体类精确清除缓存，而不是清除所有缓存。

#### 优化后的缓存清除逻辑：

```php
public function clearEntityCache(string $entityClass): void
{
    // 生成该实体类的缓存键前缀
    $className = substr(strrchr($entityClass, '\\'), 1);
    $hash = substr(md5($entityClass), 0, 8);
    $keyPrefix = self::CACHE_PREFIX . $className . '_' . $hash;
    
    // 获取所有缓存项并删除匹配的项
    $cacheItems = $this->cache->getItems([]);
    foreach ($cacheItems as $key => $item) {
        if (strpos($key, $keyPrefix) === 0) {
            $this->cache->deleteItem($key);
        }
    }
    
    // 如果上述方法不可用，则使用遍历方式
    try {
        // 尝试使用反射获取缓存池的内部存储
        $reflection = new \ReflectionClass($this->cache);
        if ($reflection->hasProperty('values')) {
            $valuesProperty = $reflection->getProperty('values');
            $valuesProperty->setAccessible(true);
            $values = $valuesProperty->getValue($this->cache);
            
            foreach (array_keys($values) as $key) {
                if (strpos($key, $keyPrefix) === 0) {
                    $this->cache->deleteItem($key);
                }
            }
        }
    } catch (\Exception $e) {
        // 如果精确清除失败，回退到清除所有缓存
        $this->cache->clear();
    }
}
```

### 3. 控制器代码简化

移除控制器中的手动缓存清除调用，简化代码逻辑：

#### 修改前：
```php
$em->persist($position);
$em->flush();

// 清除岗位相关的缓存
$this->dataGridService->clearEntityCache(Position::class);

$this->addFlash('success', '岗位创建成功');
```

#### 修改后：
```php
$em->persist($position);
$em->flush();

$this->addFlash('success', '岗位创建成功');
```

## 优势

### 1. 自动化管理
- **无需手动调用**：开发者不需要记住在每个修改岗位的地方添加缓存清除代码
- **防止遗漏**：通过事件监听器确保所有岗位数据变更都会触发缓存清除
- **代码简洁**：控制器代码更加简洁，专注于业务逻辑

### 2. 性能优化
- **精确清除**：只清除特定实体类相关的缓存，而不是全部缓存
- **减少影响**：其他实体的缓存不受影响，提高系统整体性能
- **智能回退**：如果精确清除失败，自动回退到全量清除，确保数据一致性

### 3. 可扩展性
- **模式复用**：可以为其他实体（如部门、公司等）创建类似的事件监听器
- **统一管理**：所有实体的缓存管理都遵循相同的模式
- **易于维护**：缓存逻辑集中在事件监听器中，便于维护和调试

## 使用方法

### 开发者使用

开发者在修改岗位数据时，只需要正常使用 Doctrine 的 `persist()` 和 `flush()` 方法，缓存会自动清除：

```php
// 创建岗位
$position = new Position();
$position->setName('新岗位');
$em->persist($position);
$em->flush(); // 自动触发缓存清除

// 更新岗位
$position->setName('更新后的岗位名称');
$em->flush(); // 自动触发缓存清除

// 删除岗位
$em->remove($position);
$em->flush(); // 自动触发缓存清除
```

### 扩展到其他实体

如需为其他实体添加类似的缓存管理，只需创建对应的事件监听器：

```php
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Department::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Department::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: Department::class)]
class DepartmentCacheListener
{
    // 类似的实现...
}
```

## 注意事项

1. **事件监听器注册**：Symfony 会自动注册使用 `AsEntityListener` 属性的监听器
2. **缓存适配器兼容性**：精确清除功能依赖于缓存适配器的实现，如果不支持会自动回退到全量清除
3. **性能监控**：建议在生产环境中监控缓存清除的性能影响
4. **调试支持**：可以通过日志记录缓存清除操作，便于调试和监控

## 总结

通过引入事件监听器和改进缓存清除机制，我们解决了手动调用容易遗漏和全量清除效率低的问题。新的方案提供了自动化、精确、高效的缓存管理，同时保持了代码的简洁性和可维护性。