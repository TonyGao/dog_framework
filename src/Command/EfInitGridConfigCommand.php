<?php

namespace App\Command;

use App\Entity\Platform\Entity;
use App\Entity\Organization\Position;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ef:init-grid-config',
    description: '为实体初始化默认的表格配置信息',
)]
class EfInitGridConfigCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addOption('entity', null, InputOption::VALUE_REQUIRED, '指定要初始化表格配置的实体类名称')
            ->addOption('all', null, InputOption::VALUE_NONE, '为所有实体初始化表格配置');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entityName = $input->getOption('entity');
        $all = $input->getOption('all');

        if ($entityName) {
            $this->initGridConfigForEntity($entityName, $io);
        } elseif ($all) {
            $this->initGridConfigForAllEntities($io);
        } else {
            $io->error('请指定 --entity 参数或使用 --all 选项');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function initGridConfigForEntity(string $entityName, SymfonyStyle $io): void
    {
        $entityRepo = $this->em->getRepository(Entity::class);
        $entity = $entityRepo->findOneBy(['className' => $entityName]);

        if (!$entity) {
            $io->error(sprintf('未找到实体: %s', $entityName));
            return;
        }

        $gridConfig = $this->getDefaultGridConfig($entityName);
        if ($gridConfig) {
            $entity->setGridConfig($gridConfig);
            $this->em->persist($entity);
            $this->em->flush();
            $io->success(sprintf('已为实体 %s 设置默认表格配置', $entityName));
        } else {
            $io->warning(sprintf('实体 %s 没有预定义的表格配置', $entityName));
        }
    }

    private function initGridConfigForAllEntities(SymfonyStyle $io): void
    {
        $entityRepo = $this->em->getRepository(Entity::class);
        $entities = $entityRepo->findAll();

        $count = 0;
        foreach ($entities as $entity) {
            $gridConfig = $this->getDefaultGridConfig($entity->getClassName());
            if ($gridConfig) {
                $entity->setGridConfig($gridConfig);
                $this->em->persist($entity);
                $count++;
            }
        }

        $this->em->flush();
        $io->success(sprintf('已为 %d 个实体设置默认表格配置', $count));
    }

    private function getDefaultGridConfig(string $entityName): ?array
    {
        switch ($entityName) {
            case 'Position':
                return [
                    'columns' => [
                        ['field' => 'id', 'label' => 'ID', 'visible' => true, 'width' => 80, 'sortable' => true],
                        ['field' => 'name', 'label' => '岗位名称', 'visible' => true, 'width' => 150, 'sortable' => true],
                        ['field' => 'code', 'label' => '岗位编码', 'visible' => true, 'width' => 120, 'sortable' => true],
                        ['field' => 'department', 'label' => '所属部门', 'visible' => true, 'width' => 150, 'sortable' => false, 'type' => 'relation', 'displayField' => 'name'],
                        ['field' => 'level', 'label' => '岗位级别', 'visible' => true, 'width' => 120, 'sortable' => false, 'type' => 'relation', 'displayField' => 'name'],
                        ['field' => 'state', 'label' => '状态', 'visible' => true, 'width' => 80, 'sortable' => true, 'type' => 'boolean', 'trueText' => '启用', 'falseText' => '禁用'],
                        ['field' => 'sortOrder', 'label' => '排序', 'visible' => false, 'width' => 80, 'sortable' => true],
                        ['field' => 'createdAt', 'label' => '创建时间', 'visible' => false, 'width' => 150, 'sortable' => true, 'type' => 'datetime'],
                        ['field' => 'actions', 'label' => '操作', 'visible' => true, 'width' => 150, 'sortable' => false, 'type' => 'actions']
                    ],
                    'sort' => [['field' => 'sortOrder', 'order' => 'asc'], ['field' => 'id', 'order' => 'desc']],
                    'pageSize' => 20,
                    'pageSizeOptions' => [10, 20, 50, 100]
                ];

            case 'Department':
                return [
                    'columns' => [
                        ['field' => 'id', 'label' => 'ID', 'visible' => true, 'width' => 80, 'sortable' => true],
                        ['field' => 'name', 'label' => '部门名称', 'visible' => true, 'width' => 150, 'sortable' => true],
                        ['field' => 'code', 'label' => '部门编码', 'visible' => true, 'width' => 120, 'sortable' => true],
                        ['field' => 'parent', 'label' => '上级部门', 'visible' => true, 'width' => 150, 'sortable' => false, 'type' => 'relation', 'displayField' => 'name'],
                        ['field' => 'state', 'label' => '状态', 'visible' => true, 'width' => 80, 'sortable' => true, 'type' => 'boolean', 'trueText' => '启用', 'falseText' => '禁用'],
                        ['field' => 'sortOrder', 'label' => '排序', 'visible' => false, 'width' => 80, 'sortable' => true],
                        ['field' => 'actions', 'label' => '操作', 'visible' => true, 'width' => 150, 'sortable' => false, 'type' => 'actions']
                    ],
                    'sort' => [['field' => 'sortOrder', 'order' => 'asc']],
                    'pageSize' => 20,
                    'pageSizeOptions' => [10, 20, 50, 100]
                ];

            default:
                return null;
        }
    }
}