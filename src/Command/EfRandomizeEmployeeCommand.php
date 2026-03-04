<?php

namespace App\Command;

use App\Entity\Organization\Department;
use App\Entity\Organization\Employee;
use App\Entity\Organization\Position;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ef:employee:randomize',
    description: '随机分配员工的部门和职位'
)]
class EfRandomizeEmployeeCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $employees = $this->entityManager->getRepository(Employee::class)->findAll();
        $departments = $this->entityManager->getRepository(Department::class)->findBy(['type' => 'department']);
        $positions = $this->entityManager->getRepository(Position::class)->findAll();

        if (empty($employees)) {
            $io->warning('没有找到员工数据');
            return Command::SUCCESS;
        }

        if (empty($departments)) {
            $io->error('没有找到部门数据');
            return Command::FAILURE;
        }

        if (empty($positions)) {
            $io->error('没有找到职位数据');
            return Command::FAILURE;
        }

        $io->progressStart(count($employees));

        foreach ($employees as $employee) {
            // 随机分配部门
            $randomDept = $departments[array_rand($departments)];
            $employee->setDepartment($randomDept);
            
            // 设置所属公司（部门的父级如果是公司，则设置为该父级；否则递归查找）
            // 这里简单处理，假设部门的父级就是公司
            // 实际上 Department entity 可能有 getCompany() 方法或者通过 parent 查找
            // 暂时只设置 department，因为 EmployeeController.php 中 list 方法 join 了 department
            
            // 随机分配职位
            $randomPosition = $positions[array_rand($positions)];
            $employee->setPosition($randomPosition);

            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->success(sprintf('成功更新了 %d 名员工的部门和职位数据', count($employees)));

        return Command::SUCCESS;
    }
}
