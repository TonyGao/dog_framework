<?php

namespace App\Command;

use App\Entity\Platform\View;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'ef:restore-views',
    description: '从文件系统恢复视图数据到数据库',
)]
class EfRestoreViewsCommand extends Command
{
    private EntityManagerInterface $em;
    private ParameterBagInterface $params;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $params)
    {
        parent::__construct();
        $this->em = $em;
        $this->params = $params;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('开始恢复视图数据...');

        $repo = $this->em->getRepository(View::class);

        // 1. 确保 Root 节点存在
        $root = $repo->findOneBy(['name' => 'root']);
        if (!$root) {
            $root = new View();
            $root->setName('root')
                ->setLabel('Root')
                ->setType('root');
            $this->em->persist($root);
            $this->em->flush();
            $io->success('创建 Root 节点');
        } else {
            $io->note('Root 节点已存在');
        }

        // 2. 扫描 templates/views 目录
        $basePath = $this->params->get('kernel.project_dir') . '/templates/views';
        if (!is_dir($basePath)) {
            $io->error('目录不存在: ' . $basePath);
            return Command::FAILURE;
        }

        $finder = new Finder();
        $finder->files()->in($basePath)->name('*.design.twig');

        if (!$finder->hasResults()) {
            $io->warning('未找到任何 .design.twig 文件');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($finder as $file) {
            $relativePath = $file->getRelativePath(); // e.g., 组织架构/post_management/1_0
            $fileName = $file->getFilename(); // e.g., post_management.design.twig
            
            // 解析路径
            $pathParts = explode('/', $relativePath);
            
            // 验证路径结构至少要有两层: ViewName/Version
            if (count($pathParts) < 2) {
                $io->warning("跳过不符合结构的路径: $relativePath");
                continue;
            }

            $version = array_pop($pathParts); // 1_0
            $viewName = array_pop($pathParts); // post_management
            $folders = $pathParts; // ['组织架构'] (可能为空)

            // 检查文件名是否匹配视图名 (忽略大小写?)
            // 这里假设严格匹配
            $expectedFileName = $viewName . '.design.twig';
            if ($fileName !== $expectedFileName) {
                $io->warning("文件名不匹配视图名，跳过: $fileName (期望: $expectedFileName)");
                continue;
            }

            // 3. 递归处理文件夹
            $currentParent = $root;
            $currentPath = '';

            foreach ($folders as $folderName) {
                if ($currentPath !== '') {
                    $currentPath .= '/';
                }
                $currentPath .= $folderName;

                $folder = $repo->findOneBy([
                    'name' => $folderName,
                    'parent' => $currentParent,
                    'type' => 'folder'
                ]);

                if (!$folder) {
                    $folder = new View();
                    $folder->setName($folderName)
                        ->setLabel($folderName)
                        ->setType('folder')
                        ->setParent($currentParent)
                        ->setPath($currentPath);
                    
                    $this->em->persist($folder);
                    $this->em->flush(); // 需要立即 flush 以获取 ID 用于下一次查询
                    $io->text("创建文件夹: $folderName");
                }
                $currentParent = $folder;
            }

            // 4. 处理视图节点
            $view = $repo->findOneBy([
                'name' => $viewName,
                'parent' => $currentParent,
                'type' => 'view'
            ]);

            if (!$view) {
                $view = new View();
                $view->setName($viewName)
                    ->setLabel($viewName)
                    ->setType('view')
                    ->setParent($currentParent);
                
                $io->text("创建视图: $viewName");
            }

            // 更新 Path (包含版本)
            $viewPath = $currentPath ? ($currentPath . '/' . $viewName) : $viewName;
            $viewPath .= '/' . $version;
            
            $view->setPath($viewPath);
            
            $this->em->persist($view);
            $this->em->flush();
            $count++;
        }

        // 5. 尝试修复树结构 (Gedmo Tree 有时需要)
        // $repo->recover();
        // $this->em->flush();

        $io->success("成功恢复 $count 个视图");

        return Command::SUCCESS;
    }
}
