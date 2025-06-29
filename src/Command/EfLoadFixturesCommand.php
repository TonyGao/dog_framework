<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'ef:fixtures:load',
    description: '智能加载 fixtures 数据，支持指定单个文件或全部加载'
)]
class EfLoadFixturesCommand extends Command
{
    private Filesystem $filesystem;
    private string $fixturesPath;
    private string $tempPath;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->filesystem = new Filesystem();
        $this->fixturesPath = __DIR__ . '/../../fixtures/dev';
        $this->tempPath = __DIR__ . '/../../fixtures/.temp';
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::OPTIONAL, '指定要加载的 fixture 文件名（不含路径）')
            ->addOption('all', 'a', InputOption::VALUE_NONE, '加载所有 fixtures 并清空数据库')
            ->addOption('append', null, InputOption::VALUE_NONE, '追加模式，不清空数据库')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, '覆盖模式，清空数据库后加载（去掉 --append）')
            ->setHelp(<<<'EOF'
这个命令提供了灵活的 fixtures 加载方式：

<info>加载指定文件：</info>
  <comment>php bin/console ef:fixtures:load position.yaml</comment>
  只加载 position.yaml，其他文件会被临时移动

<info>加载所有文件：</info>
  <comment>php bin/console ef:fixtures:load --all</comment>
  加载所有 fixtures 并清空数据库

<info>追加模式：</info>
  <comment>php bin/console ef:fixtures:load position.yaml --append</comment>
  追加数据，不清空数据库（可能有冲突风险）

<info>覆盖模式：</info>
  <comment>php bin/console ef:fixtures:load position.yaml --overwrite</comment>
  清空数据库后加载指定文件（强制覆盖）

<info>指定环境：</info>
  <comment>php bin/console ef:fixtures:load position.yaml --env=test</comment>
  在指定环境下执行
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');
        $loadAll = $input->getOption('all');
        $append = $input->getOption('append');
        $overwrite = $input->getOption('overwrite');
        $env = $input->getOption('env') ?: 'dev';

        // 验证参数
        if (!$file && !$loadAll) {
            $io->error('请指定要加载的文件名或使用 --all 选项加载所有文件');
            return Command::FAILURE;
        }

        if ($file && $loadAll) {
            $io->error('不能同时指定文件和 --all 选项');
            return Command::FAILURE;
        }

        if ($append && $overwrite) {
            $io->error('不能同时使用 --append 和 --overwrite 选项');
            return Command::FAILURE;
        }

        try {
            if ($loadAll) {
                return $this->loadAllFixtures($io, $append, $env);
            } else {
                return $this->loadSingleFixture($io, $file, $append, $overwrite, $env);
            }
        } catch (\Exception $e) {
            $io->error('执行失败: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function loadAllFixtures(SymfonyStyle $io, bool $append, string $env): int
    {
        $io->title('加载所有 Fixtures');
        
        // 使用统一的 executeFixturesLoad 方法
        return $this->executeFixturesLoad($append, $env, $io, false, false);
    }

    private function loadSingleFixture(SymfonyStyle $io, string $file, bool $append, bool $overwrite, string $env): int
    {
        $io->title('加载单个 Fixture: ' . $file);
        
        $targetFile = $this->fixturesPath . '/' . $file;
        
        // 验证文件存在
        if (!$this->filesystem->exists($targetFile)) {
            $io->error("文件不存在: {$targetFile}");
            return Command::FAILURE;
        }
        
        $movedFiles = [];
        
        try {
            // 创建临时目录
            $this->filesystem->mkdir($this->tempPath);
            
            // 移动其他文件到临时目录
            $movedFiles = $this->moveOtherFiles($file, $io);
            
            // 执行 fixtures 加载
            $result = $this->executeFixturesLoad($append, $env, $io, true, $overwrite, $file);
            
            return $result;
            
        } finally {
            // 无论成功失败，都要恢复文件
            $this->restoreFiles($movedFiles, $io);
        }
    }

    private function moveOtherFiles(string $keepFile, SymfonyStyle $io): array
    {
        $movedFiles = [];
        $files = glob($this->fixturesPath . '/*.yaml');
        
        foreach ($files as $file) {
            $filename = basename($file);
            if ($filename !== $keepFile) {
                $tempFile = $this->tempPath . '/' . $filename;
                $this->filesystem->rename($file, $tempFile);
                $movedFiles[] = $filename;
                $io->text("临时移动: {$filename}");
            }
        }
        
        return $movedFiles;
    }

    private function executeFixturesLoad(bool $append, string $env, SymfonyStyle $io, bool $isSingleFile = false, bool $overwrite = false, string $fixtureFile = ''): int
    {
        $command = [
            'php', 'bin/console', 'hautelook:fixtures:load',
            '--env=' . $env,
            '--no-interaction'
        ];
        
        // 处理 append 参数的逻辑
        if ($overwrite) {
            // 覆盖模式：清空指定实体数据表，然后使用 append 模式加载
            if ($fixtureFile) {
                $this->clearEntityTables($fixtureFile, $io);
                $io->success('已清空指定实体的数据表，开始加载新数据');
                // 清空后使用 append 模式加载，避免 hautelook 清空整个数据库
                $command[] = '--append';
            } else {
                $io->note('覆盖模式：将清空数据库后加载数据');
            }
        } elseif ($append || $isSingleFile) {
            // 追加模式或单个文件加载时使用 append 模式
            $command[] = '--append';
            if ($isSingleFile && !$append) {
                $io->note('单个文件加载模式：自动启用 --append 参数以保护现有数据');
            }
        }
        
        $io->note('执行命令: ' . implode(' ', $command));
        
        $process = new Process($command);
        $process->setTimeout(300);
        $process->run();
        
        if ($process->isSuccessful()) {
            $io->success('Fixture 加载完成');
            return Command::SUCCESS;
        } else {
            $io->error('加载失败: ' . $process->getErrorOutput());
            return Command::FAILURE;
        }
    }

    private function restoreFiles(array $movedFiles, SymfonyStyle $io): void
    {
        foreach ($movedFiles as $filename) {
            $tempFile = $this->tempPath . '/' . $filename;
            $originalFile = $this->fixturesPath . '/' . $filename;
            
            if ($this->filesystem->exists($tempFile)) {
                $this->filesystem->rename($tempFile, $originalFile);
                $io->text("恢复文件: {$filename}");
            }
        }
        
        // 清理临时目录
        if ($this->filesystem->exists($this->tempPath)) {
            $this->filesystem->remove($this->tempPath);
        }
    }

    /**
     * 解析 YAML 文件获取实体类型并清空对应数据表
     */
    private function clearEntityTables(string $fixtureFile, SymfonyStyle $io): void
    {
        $filePath = $this->fixturesPath . '/' . $fixtureFile;
        
        if (!$this->filesystem->exists($filePath)) {
            $io->warning("文件不存在: {$filePath}");
            return;
        }

        try {
            $yamlContent = Yaml::parseFile($filePath);
            $entityClasses = $this->extractEntityClasses($yamlContent);
            
            if (empty($entityClasses)) {
                $io->warning('未找到任何实体类定义');
                return;
            }
            
            // 按照依赖关系排序，先清空依赖表，再清空被依赖表
            $sortedClasses = $this->sortEntitiesByDependency($entityClasses);
            
            foreach ($sortedClasses as $entityClass) {
                $this->truncateEntityTable($entityClass, $io);
            }
        } catch (\Exception $e) {
            $io->warning('解析 YAML 文件失败: ' . $e->getMessage());
        }
    }

    /**
     * 从 YAML 内容中提取实体类
     */
    private function extractEntityClasses(array $yamlContent): array
    {
        $entityClasses = [];
        
        foreach ($yamlContent as $key => $value) {
            // 检查是否为实体类定义格式 (App\Entity\...)
            if (is_string($key) && strpos($key, 'App\\Entity\\') === 0) {
                $entityClasses[] = $key;
            }
        }
        
        return array_unique($entityClasses);
    }

    /**
     * 根据依赖关系对实体类进行排序
     * 返回按照清空顺序排列的实体类数组（依赖表在前，被依赖表在后）
     */
    private function sortEntitiesByDependency(array $entityClasses): array
    {
        // 定义组织架构实体的依赖关系优先级
        // 数字越小优先级越高（越先清空）
        $dependencyOrder = [
            'App\\Entity\\Organization\\Position' => 1,        // 岗位依赖其他所有表
            'App\\Entity\\Organization\\Department' => 2,     // 部门被岗位依赖
            'App\\Entity\\Organization\\PositionLevel' => 3, // 岗位级别被岗位依赖
            'App\\Entity\\Organization\\Company' => 4,       // 公司被部门和岗位依赖
            'App\\Entity\\Organization\\Corporation' => 5,   // 集团被公司依赖
        ];
        
        // 按照依赖顺序排序
        usort($entityClasses, function($a, $b) use ($dependencyOrder) {
            $orderA = $dependencyOrder[$a] ?? 999; // 未定义的实体放在最后
            $orderB = $dependencyOrder[$b] ?? 999;
            return $orderA <=> $orderB;
        });
        
        return $entityClasses;
    }

    /**
     * 清空指定实体对应的数据表
     */
    private function truncateEntityTable(string $entityClass, SymfonyStyle $io): void
    {
        try {
            $metadata = $this->entityManager->getClassMetadata($entityClass);
            $tableName = $metadata->getTableName();
            $connection = $this->entityManager->getConnection();
            $platform = $connection->getDatabasePlatform();
            
            // 根据数据库类型处理外键约束
            if ($platform->getName() === 'mysql') {
                // MySQL: 禁用外键检查
                $connection->exec('SET FOREIGN_KEY_CHECKS = 0');
                $connection->exec("TRUNCATE TABLE `{$tableName}`");
                $connection->exec('SET FOREIGN_KEY_CHECKS = 1');
            } elseif ($platform->getName() === 'postgresql') {
                // PostgreSQL: 使用 DELETE 而不是 TRUNCATE 避免级联删除
                $connection->exec("DELETE FROM \"{$tableName}\"");
                // 重置序列（如果有自增主键）
                try {
                    $connection->exec("ALTER SEQUENCE {$tableName}_id_seq RESTART WITH 1");
                } catch (\Exception $e) {
                    // 如果没有序列则忽略错误
                }
            } else {
                // 其他数据库: 使用标准 TRUNCATE
                $connection->exec("TRUNCATE TABLE {$tableName}");
            }
            
            $io->text("已清空表: {$tableName}");
        } catch (\Exception $e) {
            $io->warning("清空表失败 ({$entityClass}): " . $e->getMessage());
        }
    }
}