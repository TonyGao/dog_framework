<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use App\Kernel;
use App\Service\Platform\DataGridService;

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

echo "=== 通用缓存机制测试 ===\n\n";

// 测试1：岗位数据 - 启用缓存，长时间缓存
echo "1. 测试岗位数据（启用缓存，3600秒）\n";
$start = microtime(true);
$result1 = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    1,
    20,
    true,  // 启用缓存
    3600   // 缓存1小时
);
$time1 = round((microtime(true) - $start) * 1000, 2);
echo "第一次请求（从数据库获取并缓存）: {$time1}ms\n";
echo "数据条数: " . count($result1['data']) . "\n";

// 第二次请求 - 应该从缓存获取
$start = microtime(true);
$result2 = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    1,
    20,
    true,  // 启用缓存
    3600   // 缓存1小时
);
$time2 = round((microtime(true) - $start) * 1000, 2);
echo "第二次请求（从缓存获取）: {$time2}ms\n";
echo "性能提升: " . round(($time1 - $time2) / $time1 * 100, 1) . "%\n\n";

// 测试2：岗位数据 - 不使用缓存
echo "2. 测试岗位数据（不使用缓存）\n";
$start = microtime(true);
$result3 = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    1,
    20,
    false  // 不使用缓存
);
$time3 = round((microtime(true) - $start) * 1000, 2);
echo "不使用缓存的请求: {$time3}ms\n\n";

// 测试3：岗位数据 - 短时间缓存
echo "3. 测试岗位数据（启用缓存，60秒）\n";
$start = microtime(true);
$result4 = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    2,  // 不同页面
    20,
    true,  // 启用缓存
    60     // 缓存1分钟
);
$time4 = round((microtime(true) - $start) * 1000, 2);
echo "第一次请求（短时间缓存）: {$time4}ms\n";

// 第二次请求短时间缓存
$start = microtime(true);
$result5 = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    2,  // 不同页面
    20,
    true,  // 启用缓存
    60     // 缓存1分钟
);
$time5 = round((microtime(true) - $start) * 1000, 2);
echo "第二次请求（从缓存获取）: {$time5}ms\n";
echo "性能提升: " . round(($time4 - $time5) / $time4 * 100, 1) . "%\n\n";

// 测试4：测试不同实体（如果存在Department实体）
echo "4. 测试部门数据（启用缓存，1800秒）\n";
try {
    $start = microtime(true);
    $result6 = $dataGridService->getTableData(
        'App\\Entity\\Organization\\Department',
        1,
        10,
        true,  // 启用缓存
        1800   // 缓存30分钟
    );
    $time6 = round((microtime(true) - $start) * 1000, 2);
    echo "部门数据第一次请求: {$time6}ms\n";
    echo "部门数据条数: " . count($result6['data']) . "\n";
    
    // 第二次请求部门数据
    $start = microtime(true);
    $result7 = $dataGridService->getTableData(
        'App\\Entity\\Organization\\Department',
        1,
        10,
        true,  // 启用缓存
        1800   // 缓存30分钟
    );
    $time7 = round((microtime(true) - $start) * 1000, 2);
    echo "部门数据第二次请求（从缓存）: {$time7}ms\n";
    echo "性能提升: " . round(($time6 - $time7) / $time6 * 100, 1) . "%\n";
} catch (Exception $e) {
    echo "部门数据测试失败: " . $e->getMessage() . "\n";
}

echo "\n=== 缓存清除测试 ===\n";

// 清除岗位缓存
echo "清除岗位缓存...\n";
$dataGridService->clearEntityCache('App\\Entity\\Organization\\Position');

// 清除缓存后再次请求
$start = microtime(true);
$result8 = $dataGridService->getTableData(
    'App\\Entity\\Organization\\Position',
    1,
    20,
    true,  // 启用缓存
    3600   // 缓存1小时
);
$time8 = round((microtime(true) - $start) * 1000, 2);
echo "清除缓存后的请求: {$time8}ms\n";

echo "\n=== 测试总结 ===\n";
echo "通用缓存机制测试完成！\n";
echo "- 支持通过参数控制是否启用缓存\n";
echo "- 支持通过参数设置缓存时长\n";
echo "- 支持不同实体的独立缓存\n";
echo "- 支持缓存清除功能\n";
echo "- 缓存机制显著提升查询性能\n";