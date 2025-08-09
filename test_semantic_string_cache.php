<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use App\Service\Platform\DataGridService;
use App\Service\Platform\CacheConfig;
use Symfony\Component\Dotenv\Dotenv;

// 加载环境变量
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// 创建内核
$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

// 获取服务
$dataGridService = $container->get(DataGridService::class);

echo "=== 语义化字符串缓存配置测试 ===\n\n";

// 测试不同的语义化字符串格式
$testCases = [
    'cached 3 hours',
    'cached 30 minutes', 
    'cached 2 days',
    'cached 1.5 hours',
    'disabled',
    'no cache',
    '5 minutes',
    '2 hours',
    '1 day'
];

foreach ($testCases as $cacheString) {
    echo "测试缓存配置: '{$cacheString}'\n";
    
    $startTime = microtime(true);
    
    try {
        $result = $dataGridService->getTableData(
            'App\\Entity\\Organization\\Position',
            1,
            10,
            $cacheString  // 直接使用语义化字符串
        );
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // 解析配置以显示详细信息
        $config = CacheConfig::fromString($cacheString);
        $humanString = $config->toHumanString();
        
        echo "  - 解析结果: {$humanString}\n";
        echo "  - 缓存状态: " . ($config->isEnabled() ? '启用' : '禁用') . "\n";
        if ($config->isEnabled()) {
            echo "  - 缓存时长: {$config->getTtl()} 秒\n";
        }
        echo "  - 执行时间: " . number_format($executionTime, 2) . " ms\n";
        echo "  - 数据条数: " . count($result['data']) . "\n";
        echo "  - 总记录数: {$result['totalItems']}\n";
        
    } catch (Exception $e) {
        echo "  - 错误: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// 测试性能对比
echo "=== 性能对比测试 ===\n\n";

// 清除缓存
$dataGridService->clearAllCache();

// 测试无缓存
echo "1. 无缓存测试:\n";
$times = [];
for ($i = 0; $i < 3; $i++) {
    $startTime = microtime(true);
    $result = $dataGridService->getTableData(
        'App\\Entity\\Organization\\Position',
        1,
        10,
        'disabled'
    );
    $endTime = microtime(true);
    $times[] = ($endTime - $startTime) * 1000;
}
$avgTime = array_sum($times) / count($times);
echo "   平均执行时间: " . number_format($avgTime, 2) . " ms\n\n";

// 测试有缓存（第一次）
echo "2. 缓存测试（第一次，缓存未命中）:\n";
$startTime = microtime(true);
$result = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    1,
    10,
    'cached 1 hour'
);
$endTime = microtime(true);
$firstCacheTime = ($endTime - $startTime) * 1000;
echo "   执行时间: " . number_format($firstCacheTime, 2) . " ms\n\n";

// 测试有缓存（第二次）
echo "3. 缓存测试（第二次，缓存命中）:\n";
$times = [];
for ($i = 0; $i < 3; $i++) {
    $startTime = microtime(true);
    $result = $dataGridService->getTableData(
        'App\\Entity\\Organization\\Position',
        1,
        10,
        'cached 1 hour'
    );
    $endTime = microtime(true);
    $times[] = ($endTime - $startTime) * 1000;
}
$avgCacheTime = array_sum($times) / count($times);
echo "   平均执行时间: " . number_format($avgCacheTime, 2) . " ms\n";

$improvement = (($avgTime - $avgCacheTime) / $avgTime) * 100;
echo "   性能提升: " . number_format($improvement, 1) . "%\n\n";

// 测试复杂的语义化字符串
echo "=== 复杂语义化字符串测试 ===\n\n";

$complexCases = [
    'cached 2.5 hours',
    'cached 90 minutes',
    'cached 1 week',
    'cached 0.5 days',
    '45 seconds'
];

foreach ($complexCases as $cacheString) {
    echo "测试: '{$cacheString}'\n";
    $config = CacheConfig::fromString($cacheString);
    echo "  - 解析为: {$config->toHumanString()}\n";
    echo "  - TTL: {$config->getTtl()} 秒\n";
    echo "  - 状态: " . ($config->isEnabled() ? '启用' : '禁用') . "\n\n";
}

echo "=== 推荐的第三方库 ===\n\n";
echo "如果需要更强大的时间解析功能，推荐以下PHP库:\n\n";
echo "1. khill/php-duration\n";
echo "   - Composer: composer require khill/php-duration\n";
echo "   - 功能: 支持多种时间格式解析和转换\n";
echo "   - 示例: \$duration = new Duration('1h 2m 5s'); \$seconds = \$duration->toSeconds();\n\n";

echo "2. nesbot/carbon\n";
echo "   - Composer: composer require nesbot/carbon\n";
echo "   - 功能: 强大的日期时间处理库，包含CarbonInterval\n";
echo "   - 示例: \$interval = CarbonInterval::fromString('1 hour'); \$seconds = \$interval->totalSeconds;\n\n";

echo "3. 原生PHP DateInterval\n";
echo "   - 内置类，无需安装\n";
echo "   - 功能: 基础的时间间隔处理\n";
echo "   - 示例: \$interval = new DateInterval('PT1H'); // 1小时\n\n";

echo "当前实现的CacheConfig类已经支持常见的语义化时间格式，\n";
echo "如果需要更复杂的解析功能，可以集成上述库来增强解析能力。\n";

echo "\n=== 测试完成 ===\n";