# Deprecation 警告管理指南

本文档说明如何管理和关闭 Symfony 项目中的 deprecation 警告日志。

## 问题描述

在 PHP 8.1+ 版本中，使用隐式 nullable 参数（如 `\Throwable $exception = null`）会产生 deprecation 警告：

```
App\DataCollector\AssetDuplicationCollector::collect(): Implicitly marking parameter $exception as nullable is deprecated, the explicit nullable type must be used instead
```

## 已修复的文件

我们已经修复了以下文件中的 deprecation 警告：

### 1. AssetDuplicationCollector.php
```php
// 修复前
public function collect(Request $request, Response $response, \Throwable $exception = null)

// 修复后
public function collect(Request $request, Response $response, ?\Throwable $exception = null)
```

### 2. MenuStaticGenerator.php
```php
// 修复前
public function generateStaticMenu(SymfonyStyle $io = null): void

// 修复后
public function generateStaticMenu(?SymfonyStyle $io = null): void
```

### 3. FormFieldBuilderService.php
```php
// 修复前
public function buildFields(FormBuilderInterface $builder, string $entityClass, callable $customOptionsCallback = null): void

// 修复后
public function buildFields(FormBuilderInterface $builder, string $entityClass, ?callable $customOptionsCallback = null): void
```

### 4. EntityService.php
```php
// 修复前
private function handleEPGSave($entity, string $type, $parent = null)
public function convertEntityToPropertyGroupData(Entity $entity, $type, $parent = null)

// 修复后
private function handleEPGSave($entity, string $type, mixed $parent = null)
public function convertEntityToPropertyGroupData(Entity $entity, mixed $type, mixed $parent = null)
```

## 日志配置管理

### 当前配置

我们已经更新了 `config/packages/monolog.yaml` 配置文件：

- **开发环境**: Deprecation 警告被记录到单独的 `var/log/deprecation.log` 文件中，不会出现在主日志和控制台中
- **生产环境**: Deprecation 警告只记录到文件中，不会输出到 stderr
- **测试环境**: 继续使用默认配置

### 配置详情

```yaml
when@dev:
    monolog:
        handlers:
            main:
                channels: ["!event", "!deprecation"]  # 排除 deprecation 通道
            console:
                channels: ["!event", "!doctrine", "!console", "!deprecation"]  # 控制台也排除
            deprecation:
                type: stream
                path: "%kernel.logs_dir%/deprecation.log"  # 单独的文件
                level: warning
                channels: [deprecation]

when@prod:
    monolog:
        handlers:
            main:
                channels: ["!deprecation"]  # 主处理器排除 deprecation
            console:
                channels: ["!event", "!doctrine", "!deprecation"]  # 控制台排除
            deprecation:
                type: stream
                path: "%kernel.logs_dir%/deprecation.log"  # 记录到文件
                level: error  # 只记录错误级别
```

## 其他关闭方法

### 方法1: 环境变量设置

在 `.env` 文件中设置：
```env
APP_ENV=prod
APP_DEBUG=false
```

### 方法2: PHP 错误报告设置

在 `config/packages/framework.yaml` 中：
```yaml
framework:
    php_errors:
        log: true
        throw: false
```

### 方法3: 代码中临时关闭

```php
// 临时关闭 deprecation 警告
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
```

### 方法4: 完全禁用 deprecation 通道

如果您想完全禁用 deprecation 日志，可以在 `monolog.yaml` 中移除 `deprecation` 处理器，或者设置一个 `null` 处理器：

```yaml
monolog:
    handlers:
        deprecation:
            type: 'null'
            channels: [deprecation]
```

## 验证修复

运行测试脚本验证修复效果：

```bash
php test_deprecation_fix.php
```

## 最佳实践

1. **开发环境**: 保留 deprecation 警告，但记录到单独文件中，便于开发者查看和修复
2. **生产环境**: 将 deprecation 警告记录到文件中，避免影响应用性能
3. **定期检查**: 定期查看 `var/log/deprecation.log` 文件，及时修复新的 deprecation 问题
4. **代码审查**: 在代码审查中注意检查新增的 nullable 参数是否使用了正确的语法

## 注意事项

- 修复 deprecation 警告有助于确保代码在未来的 PHP 版本中正常工作
- 不建议完全忽略 deprecation 警告，应该及时修复
- 在升级 PHP 版本或 Symfony 版本时，特别注意新的 deprecation 警告

## 相关文件

- `config/packages/monolog.yaml` - 日志配置文件
- `test_deprecation_fix.php` - 测试脚本
- `var/log/deprecation.log` - Deprecation 警告日志文件