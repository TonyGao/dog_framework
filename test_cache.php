<?php

// 测试岗位接口缓存功能
require_once __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use App\Service\Platform\DataGridService;
use Symfony\Component\Dotenv\Dotenv;

// 加载环境变量
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '/.env');

// 启动 Symfony 内核
$kernel = new Kernel($_ENV['APP_ENV'] ?? 'dev', (bool) ($_ENV['APP_DEBUG'] ?? true));
$kernel->boot();
$container = $kernel->getContainer();

try {
    // 获取 DataGridService
    $dataGridService = $container->get(DataGridService::class);
    
    echo "=== 岗位接口缓存功能测试 ===\n\n";
    
    // 第一次请求 - 应该从数据库获取数据并缓存
    echo "第一次请求（从数据库获取并缓存）:\n";
    $start = microtime(true);
    $result1 = $dataGridService->getTableData('App\\Entity\\Organization\\Position', 1, 20);
    $time1 = microtime(true) - $start;
    echo "耗时: " . round($time1 * 1000, 2) . "ms\n";
    echo "总记录数: " . $result1['totalItems'] . "\n";
    echo "数据行数: " . count($result1['data']) . "\n\n";
    
    // 第二次请求 - 应该从缓存获取数据
    echo "第二次请求（从缓存获取）:\n";
    $start = microtime(true);
    $result2 = $dataGridService->getTableData('App\\Entity\\Organization\\Position', 1, 20);
    $time2 = microtime(true) - $start;
    echo "耗时: " . round($time2 * 1000, 2) . "ms\n";
    echo "总记录数: " . $result2['totalItems'] . "\n";
    echo "数据行数: " . count($result2['data']) . "\n\n";
    
    // 性能对比
    $speedup = $time1 / $time2;
    echo "性能提升: " . round($speedup, 2) . "倍\n\n";
    
    // 测试缓存清除功能
    echo "测试缓存清除功能:\n";
    $dataGridService->clearPositionCache();
    echo "岗位缓存已清除\n\n";
    
    // 清除缓存后再次请求
    echo "清除缓存后的请求（重新从数据库获取）:\n";
    $start = microtime(true);
    $result3 = $dataGridService->getTableData('App\\Entity\\Organization\\Position', 1, 20);
    $time3 = microtime(true) - $start;
    echo "耗时: " . round($time3 * 1000, 2) . "ms\n";
    echo "总记录数: " . $result3['totalItems'] . "\n";
    echo "数据行数: " . count($result3['data']) . "\n\n";
    
    // 测试不同页面的缓存
    echo "测试第二页缓存:\n";
    $start = microtime(true);
    $result4 = $dataGridService->getTableData('App\\Entity\\Organization\\Position', 2, 20);
    $time4 = microtime(true) - $start;
    echo "耗时: " . round($time4 * 1000, 2) . "ms\n";
    echo "总记录数: " . $result4['totalItems'] . "\n";
    echo "数据行数: " . count($result4['data']) . "\n\n";
    
    echo "=== 缓存功能测试完成 ===\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}