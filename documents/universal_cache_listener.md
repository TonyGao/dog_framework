# 通用实体缓存监听器

## 概述

通用实体缓存监听器（`UniversalEntityCacheListener`）是一个统一的缓存管理解决方案，用于自动处理实体数据变更时的缓存清除操作。相比为每个实体单独创建监听器，这种方案更加简洁、易维护且可扩展。

## 主要特性

### 1. 统一管理
- 一个监听器处理所有需要缓存清除的实体
- 通过配置文件统一管理监听的实体列表
- 避免重复代码，提高代码复用性

### 2. 配置化管理
- 在 `config/services.yaml` 中配置需要监听的实体
- 支持动态添加或移除监听的实体
- 无需修改代码即可调整监听范围

### 3. 自动化处理
- 监听实体的创建（postPersist）、更新（postUpdate）、删除（postRemove）事件
- 自动调用 `DataGridService::clearEntityCache()` 清除相关缓存
- 无需在控制器中手动调用缓存清除方法

## 配置方法

### 1. 在 services.yaml 中配置监听的实体

```yaml
parameters:
    # 缓存监听器配置
    app.cache_watched_entities:
        - 'App\Entity\Organization\Position'
        - 'App\Entity\Organization\Department'
        - 'App\Entity\Organization\Company'
        # 可以继续添加其他需要监听的实体

services:
    # 通用实体缓存监听器
    App\EventListener\Entity\UniversalEntityCacheListener:
        arguments:
            $dataGridService: '@App\Service\Platform\DataGridService'
            $watchedEntities: '%app.cache_watched_entities%'
        tags:
            - { name: doctrine.event_listener, event: postPersist }
            - { name: doctrine.event_listener, event: postUpdate }
            - { name: doctrine.event_listener, event: postRemove }
```

### 2. 添加新的监听实体

只需在 `app.cache_watched_entities` 参数中添加新的实体类名：

```yaml
parameters:
    app.cache_watched_entities:
        - 'App\Entity\Organization\Position'
        - 'App\Entity\Organization\Department'
        - 'App\Entity\Organization\Company'
        - 'App\Entity\Organization\Employee'  # 新添加的实体
        - 'App\Entity\Platform\Menu'         # 新添加的实体
```

## 使用示例

### 控制器代码简化

**之前的做法（需要手动清除缓存）：**
```php
public function createPosition(Request $request): Response
{
    $position = new Position();
    $form = $this->createForm(PositionType::class, $position);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $this->entityManager->persist($position);
        $this->entityManager->flush();
        
        // 手动清除缓存
        $this->dataGridService->clearEntityCache(Position::class);
        
        return $this->redirectToRoute('admin_org_position_list');
    }
    
    return $this->render('admin/org/position_form.html.twig', [
        'form' => $form->createView(),
    ]);
}
```

**现在的做法（自动清除缓存）：**
```php
public function createPosition(Request $request): Response
{
    $position = new Position();
    $form = $this->createForm(PositionType::class, $position);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $this->entityManager->persist($position);
        $this->entityManager->flush();
        // 缓存会自动清除，无需手动调用
        
        return $this->redirectToRoute('admin_org_position_list');
    }
    
    return $this->render('admin/org/position_form.html.twig', [
        'form' => $form->createView(),
    ]);
}
```

## 工作原理

1. **事件监听**：监听器注册了三个 Doctrine 事件：
   - `postPersist`：实体创建后触发
   - `postUpdate`：实体更新后触发
   - `postRemove`：实体删除后触发

2. **实体检查**：当事件触发时，监听器检查变更的实体是否在配置的监听列表中

3. **缓存清除**：如果实体在监听列表中，自动调用 `DataGridService::clearEntityCache()` 清除相关缓存

## 优势对比

| 特性 | 单独监听器方案 | 通用监听器方案 |
|------|----------------|----------------|
| 代码量 | 每个实体需要单独的监听器文件 | 一个监听器处理所有实体 |
| 维护性 | 需要维护多个文件 | 只需维护一个文件 |
| 扩展性 | 添加新实体需要创建新文件 | 只需修改配置文件 |
| 一致性 | 容易出现实现不一致 | 统一的处理逻辑 |
| 配置管理 | 分散在各个文件中 | 集中在配置文件中 |

## 注意事项

1. **性能考虑**：监听器会在每次实体变更时触发，对于高频操作的实体需要评估性能影响

2. **缓存策略**：确保 `DataGridService::clearEntityCache()` 方法能够正确清除相关缓存

3. **实体类名**：配置中的实体类名必须是完整的类名（包含命名空间）

4. **事件顺序**：缓存清除发生在数据库事务提交之后，确保数据一致性

## 扩展功能

监听器还提供了运行时管理方法：

```php
// 动态添加监听实体
$listener->addWatchedEntity('App\Entity\NewEntity');

// 移除监听实体
$listener->removeWatchedEntity('App\Entity\OldEntity');

// 获取当前监听的实体列表
$entities = $listener->getWatchedEntities();
```

这些方法可以在特殊情况下用于动态调整监听范围，但通常建议通过配置文件进行管理。

## 总结

通用实体缓存监听器提供了一个优雅、可维护的缓存管理解决方案。通过配置化管理和自动化处理，大大简化了缓存清除的复杂性，提高了开发效率和代码质量。