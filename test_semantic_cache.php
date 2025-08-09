<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use App\Kernel;
use App\Service\Platform\DataGridService;
use App\Service\Platform\CacheConfig;

// 加载环境变量
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// 创建内核
$kernel = new Kernel('dev', true);
$kernel->boot();

// 获取容器
$container = $kernel->getContainer();

// 获取 DataGridService
$dataGridService = $container->get(DataGridService::class);

echo "=== 语义化缓存配置测试 ===\n\n";

// 测试1：不使用缓存
echo "1. 测试不使用缓存\n";
$start = microtime(true);
$result1 = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    1,
    20
    // 不传递缓存配置，默认不使用缓存
);
$time1 = round((microtime(true) - $start) * 1000, 2);
echo "不使用缓存: {$time1}ms\n";
echo "数据条数: " . count($result1['data']) . "\n\n";

// 测试2：使用 CacheConfig::disabled()
echo "2. 测试显式禁用缓存\n";
$start = microtime(true);
$result2 = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    1,
    20,
    CacheConfig::disabled()
);
$time2 = round((microtime(true) - $start) * 1000, 2);
echo "显式禁用缓存: {$time2}ms\n\n";

// 测试3：使用 CacheConfig::hours(1)
echo "3. 测试缓存1小时\n";
$start = microtime(true);
$result3 = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    1,
    20,
    CacheConfig::hours(1)
);
$time3 = round((microtime(true) - $start) * 1000, 2);
echo "第一次请求（缓存1小时）: {$time3}ms\n";

// 第二次请求 - 应该从缓存获取
$start = microtime(true);
$result4 = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    1,
    20,
    CacheConfig::hours(1)
);
$time4 = round((microtime(true) - $start) * 1000, 2);
echo "第二次请求（从缓存获取）: {$time4}ms\n";
echo "性能提升: " . round(($time3 - $time4) / $time3 * 100, 1) . "%\n\n";

// 测试4：使用 CacheConfig::minutes(30)
echo "4. 测试缓存30分钟\n";
$start = microtime(true);
$result5 = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    2,  // 不同页面
    20,
    CacheConfig::minutes(30)
);
$time5 = round((microtime(true) - $start) * 1000, 2);
echo "缓存30分钟: {$time5}ms\n\n";

// 测试5：使用 CacheConfig::cached() 默认配置
echo "5. 测试默认缓存配置\n";
$start = microtime(true);
$result6 = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    3,  // 不同页面
    20,
    CacheConfig::cached()  // 默认1小时
);
$time6 = round((microtime(true) - $start) * 1000, 2);
echo "默认缓存配置: {$time6}ms\n\n";

// 测试6：使用语义化字符串
echo "6. 测试语义化字符串配置\n";
$start = microtime(true);
$result7 = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    4,  // 不同页面
    20,
    CacheConfig::cached('2 hours')  // 缓存2小时
);
$time7 = round((microtime(true) - $start) * 1000, 2);
echo "语义化字符串配置（2 hours）: {$time7}ms\n\n";

// 测试7：测试不同的语义化配置
echo "7. 测试各种语义化配置\n";

$configs = [
    'CacheConfig::minutes(5)' => CacheConfig::minutes(5),
    'CacheConfig::hours(2)' => CacheConfig::hours(2),
    'CacheConfig::days(1)' => CacheConfig::days(1),
    'CacheConfig::cached("30 minutes")' => CacheConfig::cached('30 minutes'),
    'CacheConfig::cached("1 hour")' => CacheConfig::cached('1 hour'),
];

foreach ($configs as $name => $config) {
    echo "配置: {$name}\n";
    echo "  - 是否启用: " . ($config->isEnabled() ? '是' : '否') . "\n";
    echo "  - 缓存时长: " . $config->getTtl() . "秒\n";
    echo "  - 策略: " . $config->getStrategy() . "\n\n";
}

echo "=== 缓存清除测试 ===\n";

// 清除岗位缓存
echo "清除岗位缓存...\n";
$dataGridService->clearEntityCache('App\\Entity\\Organization\\Position');

// 清除缓存后再次请求
$start = microtime(true);
$result8 = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    1,
    20,
    CacheConfig::hours(1)
);
$time8 = round((microtime(true) - $start) * 1000, 2);
echo "清除缓存后的请求: {$time8}ms\n";

echo "\n=== 测试总结 ===\n";
echo "语义化缓存配置测试完成！\n";
echo "\n支持的配置方式：\n";
echo "- CacheConfig::disabled() - 禁用缓存\n";
echo "- CacheConfig::cached() - 启用缓存（默认1小时）\n";
echo "- CacheConfig::minutes(30) - 缓存30分钟\n";
echo "- CacheConfig::hours(2) - 缓存2小时\n";
echo "- CacheConfig::days(1) - 缓存1天\n";
echo "- CacheConfig::cached('30 minutes') - 语义化字符串\n";
echo "- CacheConfig::cached('2 hours') - 语义化字符串\n";
echo "- CacheConfig::cached('1 day') - 语义化字符串\n";
echo "\n优势：\n";
echo "- 代码更易读，语义清晰\n";
echo "- 类型安全，避免参数错误\n";
echo "- 支持多种配置方式\n";
echo "- 向后兼容，默认不启用缓存\n";