# 语义化缓存配置指南

## 概述

`DataGridService` 现在支持使用语义化字符串来配置缓存，让代码更加直观和易读。你可以直接传入如 `'cached 3 hours'` 这样的字符串，而不需要手动创建 `CacheConfig` 对象。

## 基本用法

### 直接使用语义化字符串

```php
// 缓存3小时
$result = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    $page,
    $pageSize,
    'cached 3 hours'
);

// 缓存30分钟
$result = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    $page,
    $pageSize,
    'cached 30 minutes'
);

// 禁用缓存
$result = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    $page,
    $pageSize,
    'disabled'
);
```

## 支持的语义化格式

### 1. 带 'cached' 前缀的格式

```php
'cached 3 hours'     // 缓存3小时
'cached 30 minutes'  // 缓存30分钟
'cached 2 days'      // 缓存2天
'cached 1.5 hours'   // 缓存1.5小时
'cached 90 minutes'  // 缓存90分钟
'cached 1 week'      // 缓存1周
'cached'             // 缓存1小时（默认）
```

### 2. 直接时间格式

```php
'3 hours'      // 自动启用缓存，缓存3小时
'30 minutes'   // 自动启用缓存，缓存30分钟
'2 days'       // 自动启用缓存，缓存2天
'45 seconds'   // 自动启用缓存，缓存45秒
```

### 3. 禁用缓存格式

```php
'disabled'   // 禁用缓存
'no cache'   // 禁用缓存
'nocache'    // 禁用缓存
```

### 4. 支持的时间单位

| 单位 | 支持的格式 | 示例 |
|------|------------|------|
| 秒 | `second`, `seconds`, `sec`, `s` | `30 seconds`, `45s` |
| 分钟 | `minute`, `minutes`, `min`, `m` | `30 minutes`, `5m` |
| 小时 | `hour`, `hours`, `hr`, `h` | `3 hours`, `2h` |
| 天 | `day`, `days`, `d` | `2 days`, `1d` |
| 周 | `week`, `weeks`, `w` | `1 week`, `2w` |

### 5. 支持小数格式

```php
'cached 1.5 hours'  // 1.5小时 = 90分钟
'cached 2.5 days'   // 2.5天 = 60小时
'cached 0.5 hours'  // 0.5小时 = 30分钟
```

## 实际应用示例

### Controller 中的使用

```php
class OrgApiController extends AbstractController
{
    public function positionList(Request $request, DataGridService $dataGridService): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 20);
        
        // 岗位数据变化不频繁，缓存1小时
        $result = $dataGridService->getTableData(
            'App\\Entity\\Organization\\Position',
            $page,
            $pageSize,
            'cached 1 hour'
        );
        
        return $this->json($result);
    }
    
    public function departmentList(Request $request, DataGridService $dataGridService): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 20);
        
        // 部门数据变化较频繁，缓存15分钟
        $result = $dataGridService->getTableData(
            'App\\Entity\\Organization\\Department',
            $page,
            $pageSize,
            'cached 15 minutes'
        );
        
        return $this->json($result);
    }
    
    public function userList(Request $request, DataGridService $dataGridService): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 20);
        
        // 用户数据实时性要求高，不使用缓存
        $result = $dataGridService->getTableData(
            'App\\Entity\\OrgUser',
            $page,
            $pageSize,
            'disabled'
        );
        
        return $this->json($result);
    }
}
```

### 根据业务场景选择缓存策略

```php
// 基础数据（很少变更）- 长时间缓存
$positions = $dataGridService->getTableData($entityClass, $page, $pageSize, 'cached 1 day');
$companies = $dataGridService->getTableData($entityClass, $page, $pageSize, 'cached 12 hours');

// 业务数据（中等频率变更）- 中等时间缓存
$departments = $dataGridService->getTableData($entityClass, $page, $pageSize, 'cached 1 hour');
$projects = $dataGridService->getTableData($entityClass, $page, $pageSize, 'cached 30 minutes');

// 动态数据（频繁变更）- 短时间缓存或不缓存
$users = $dataGridService->getTableData($entityClass, $page, $pageSize, 'cached 5 minutes');
$logs = $dataGridService->getTableData($entityClass, $page, $pageSize, 'disabled');
```

## 推荐的第三方库

如果你需要更强大的时间解析功能，可以考虑集成以下PHP库：

### 1. khill/php-duration

**安装：**
```bash
composer require khill/php-duration
```

**特点：**
- 支持多种时间格式解析
- 可以处理复杂的时间表达式
- 支持冒号分隔格式（如 `2:43`）
- 支持人类可读格式（如 `6m21s`）

**示例：**
```php
use Khill\Duration\Duration;

$duration = new Duration('1h 2m 5s');
echo $duration->toSeconds(); // 3725
echo $duration->humanize();  // 1h 2m 5s
echo $duration->formatted(); // 1:02:05

// 在CacheConfig中集成
class CacheConfig {
    private function parseDurationWithLibrary($duration): int {
        if (class_exists('Khill\\Duration\\Duration')) {
            try {
                $durationObj = new Duration($duration);
                return $durationObj->toSeconds();
            } catch (Exception $e) {
                // 回退到内置解析
                return $this->parseDuration($duration);
            }
        }
        return $this->parseDuration($duration);
    }
}
```

### 2. nesbot/carbon

**安装：**
```bash
composer require nesbot/carbon
```

**特点：**
- 强大的日期时间处理库
- 包含 `CarbonInterval` 类用于时间间隔处理
- Laravel 框架默认集成

**示例：**
```php
use Carbon\CarbonInterval;

$interval = CarbonInterval::fromString('1 hour');
echo $interval->totalSeconds; // 3600

$interval = CarbonInterval::hours(2)->minutes(30);
echo $interval->totalSeconds; // 9000
```

### 3. 原生PHP DateInterval

**特点：**
- PHP内置类，无需安装
- 支持ISO 8601格式
- 基础的时间间隔处理

**示例：**
```php
// ISO 8601格式
$interval = new DateInterval('PT1H');     // 1小时
$interval = new DateInterval('PT30M');    // 30分钟
$interval = new DateInterval('P1D');      // 1天
$interval = new DateInterval('PT1H30M');  // 1小时30分钟

// 转换为秒数
function intervalToSeconds(DateInterval $interval): int {
    $reference = new DateTime();
    $endTime = clone $reference;
    $endTime->add($interval);
    return $endTime->getTimestamp() - $reference->getTimestamp();
}
```

## 性能测试结果

根据测试结果，语义化缓存配置在性能方面表现优异：

- **无缓存平均执行时间：** 2.27 ms
- **缓存命中平均执行时间：** 0.07 ms
- **性能提升：** 96.9%

## 最佳实践

### 1. 根据数据特性选择缓存时长

```php
// 静态配置数据 - 长时间缓存
'cached 1 day'     // 系统配置、字典数据
'cached 12 hours'  // 组织架构、岗位信息

// 业务数据 - 中等时间缓存
'cached 1 hour'    // 部门列表、项目信息
'cached 30 minutes' // 用户权限、菜单数据

// 动态数据 - 短时间缓存或不缓存
'cached 5 minutes' // 在线用户、统计数据
'disabled'         // 实时日志、消息通知
```

### 2. 使用语义化的时间表达

```php
// 推荐：语义化表达
'cached 3 hours'   // 清晰表达缓存3小时
'cached 30 minutes' // 清晰表达缓存30分钟

// 不推荐：数字表达
CacheConfig::cached(10800)  // 不直观，需要计算
CacheConfig::cached(1800)   // 不直观，需要计算
```

### 3. 在不同环境使用不同策略

```php
// 根据环境调整缓存策略
$cacheConfig = $_ENV['APP_ENV'] === 'prod' 
    ? 'cached 1 hour'    // 生产环境使用缓存
    : 'disabled';        // 开发环境禁用缓存

$result = $dataGridService->getTableData($entityClass, $page, $pageSize, $cacheConfig);
```

## 注意事项

1. **缓存一致性：** 当数据发生变更时，记得清除相关缓存
2. **内存使用：** 长时间缓存大量数据可能占用较多内存
3. **时间精度：** 小数时间会被转换为整数秒，可能存在精度损失
4. **错误处理：** 无法解析的时间格式会回退到默认值（1小时）

## 总结

语义化缓存配置让代码更加直观和易维护，同时保持了高性能。通过合理选择缓存策略和时长，可以在保证数据实时性的同时显著提升系统性能。如果需要更复杂的时间解析功能，可以考虑集成推荐的第三方库来增强解析能力。