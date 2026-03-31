## 方法签名

```php
public function getTableData(
    string $entityClass,
    int $page = 1,
    int $pageSize = 20,
    ?CacheConfig $cacheConfig = null
): array
```

### 参数说明

- `$entityClass`: 实体类名
- `$page`: 页码（从1开始）
- `$pageSize`: 每页数据量
- `$cacheConfig`: 缓存配置对象（默认null，不使用缓存）

### CacheConfig 配置类

`CacheConfig` 类提供了语义化的缓存配置选项：

```php
// 禁用缓存
CacheConfig::disabled()

// 启用缓存（默认1小时）
CacheConfig::cached()

// 分钟级缓存
CacheConfig::minutes(30)  // 缓存30分钟

// 小时级缓存
CacheConfig::hours(2)     // 缓存2小时

// 天级缓存
CacheConfig::days(1)      // 缓存1天

// 语义化字符串
CacheConfig::cached('30 minutes')
CacheConfig::cached('2 hours')
CacheConfig::cached('1 day')
```

## 使用示例

### 1. 不经常变更的数据（推荐长时间缓存）

适用于：岗位、部门、公司等组织架构数据

```php
// 岗位数据 - 缓存1小时
$result = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    $page,
    $pageSize,
    CacheConfig::hours(1)  // 缓存1小时
);

// 部门数据 - 缓存30分钟
$result = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Department',
    $page,
    $pageSize,
    CacheConfig::minutes(30)  // 缓存30分钟
);

// 公司数据 - 缓存1天（很少变更）
$result = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Company',
    $page,
    $pageSize,
    CacheConfig::days(1)  // 缓存1天
);
```

### 2. 经常变更的数据（短时间缓存或不缓存）

适用于：用户活动记录、订单数据、实时统计等

```php
// 用户活动记录 - 短时间缓存
$result = $dataGridService->getTableData(
    'App\\Entity\\User\\ActivityLog',
    $page,
    $pageSize,
    CacheConfig::minutes(5)  // 缓存5分钟
);

// 实时数据 - 不使用缓存
$result = $dataGridService->getTableData(
    'App\\Entity\\Realtime\\Statistics',
    $page,
    $pageSize
    // 不传递缓存配置，默认不使用缓存
);

// 或者显式禁用缓存
$result = $dataGridService->getTableData(
    'App\\Entity\\Realtime\\Statistics',
    $page,
    $pageSize,
    CacheConfig::disabled()  // 显式禁用缓存
);
```

### 3. 中等频率变更的数据（中等时间缓存）

适用于：配置数据、字典数据等

```php
// 系统配置 - 缓存15分钟
$result = $dataGridService->getTableData(
    'App\\Entity\\System\\Config',
    $page,
    $pageSize,
    CacheConfig::minutes(15)  // 缓存15分钟
);

// 字典数据 - 缓存2小时
$result = $dataGridService->getTableData(
    'App\\Entity\\System\\Dictionary',
    $page,
    $pageSize,
    CacheConfig::hours(2)  // 缓存2小时
);

// 使用语义化字符串
$result = $dataGridService->getTableData(
    'App\\Entity\\System\\Settings',
    $page,
    $pageSize,
    CacheConfig::cached('30 minutes')  // 缓存30分钟
);
```

### 4. Controller 中的使用示例

```php
class DataGridController extends AbstractController
{
    public function __construct(
        private DataGridService $dataGridService
    ) {}

    #[Route('/api/positions', methods: ['GET'])]
    public function getPositions(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 20);
        
        $result = $this->dataGridService->getTableData(
            'App\\Entity\\Organization\\Position',
            $page,
            $pageSize,
            CacheConfig::hours(1)  // 岗位数据缓存1小时
        );
        
        return $this->json($result);
    }
    
    #[Route('/api/activity-logs', methods: ['GET'])]
    public function getActivityLogs(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 20);
        
        $result = $this->dataGridService->getTableData(
            'App\\Entity\\User\\ActivityLog',
            $page,
            $pageSize,
            CacheConfig::minutes(5)  // 活动日志短时间缓存
        );
        
        return $this->json($result);
    }
}
```

## 语义化配置的优势

### 1. 代码可读性
```php
// 旧方式 - 不够直观
$result = $dataGridService->getTableData($entity, $page, $pageSize, true, 3600);

// 新方式 - 语义清晰
$result = $dataGridService->getTableData($entity, $page, $pageSize, CacheConfig::hours(1));
```

### 2. 类型安全
- 避免传递错误的参数类型
- IDE 提供更好的代码提示
- 编译时检查参数有效性

### 3. 灵活配置
```php
// 多种配置方式
CacheConfig::minutes(30)           // 数字方式
CacheConfig::cached('30 minutes')  // 字符串方式
CacheConfig::disabled()            // 禁用缓存
```

### 4. 向后兼容
- 默认不启用缓存，不影响现有代码
- 可选参数设计，渐进式升级

## 缓存管理

### 清除特定实体的缓存

```php
// 清除岗位相关的所有缓存
$dataGridService->clearEntityCache('App\\Entity\\Organization\\Position');

// 清除部门相关的所有缓存
$dataGridService->clearEntityCache('App\\Entity\\Organization\\Department');
```

### 清除所有缓存

```php
// 清除DataGridService的所有缓存
$dataGridService->clearAllCache();
```

## 性能优化建议

### 1. 缓存时间设置原则

- **静态数据**（如岗位、部门）：1-24小时
- **半静态数据**（如配置、字典）：15分钟-1小时
- **动态数据**（如日志、统计）：1-10分钟或不缓存
- **实时数据**：不使用缓存

### 2. 缓存键的设计

缓存键自动包含以下信息：
- 实体类名（简化后）
- 页码
- 每页数据量
- MD5哈希（避免键名过长）

### 3. 内存使用优化

- 合理设置缓存时间，避免缓存过多数据
- 定期清理不需要的缓存
- 监控缓存命中率

## 注意事项

1. **缓存一致性**：当数据发生变更时，记得清除相关缓存
2. **内存管理**：避免设置过长的缓存时间导致内存占用过高
3. **并发安全**：当前实现是线程安全的
4. **错误处理**：缓存失败时会自动降级到直接查询数据库
5. **配置选择**：根据数据特性选择合适的缓存策略

## 监控和调试

可以通过日志或性能监控工具来观察：
- 缓存命中率
- 查询响应时间
- 内存使用情况
- 缓存清除频率

这些指标可以帮助你优化缓存策略，提升应用性能。