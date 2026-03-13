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
            
            // 设置所属公司
            if ($randomDept->getCompany()) {
                $employee->setCompany($randomDept->getCompany());
            } else {
                // 如果部门没有直接关联公司，尝试通过上级查找（可选，视业务逻辑而定）
                // 这里假设 Department 实体的数据完整性，即部门应该关联公司
                $io->warning(sprintf('部门 %s (ID: %s) 未关联公司', $randomDept->getName(), $randomDept->getId()));
            }
            
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
