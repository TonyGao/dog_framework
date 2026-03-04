<?php

namespace App\Controller;

use App\Entity\Organization\Department;
use App\Entity\Organization\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmployeeController extends AbstractController
{
    #[Route('/employee/list', name: 'employee_list')]
    public function list(Request $request, EntityManagerInterface $em): Response
    {
        // 1. 获取组织架构树
        $deptRepo = $em->getRepository(Department::class);
        $tree = $deptRepo->childrenHierarchy(null, false, [
            'decorate' => true,
            'rootOpen' => static function (array $tree): ?string {
                if ([] !== $tree && 0 == $tree[0]['lvl']) {
                    return '<ol class="ol-left-tree">';
                }

                if ($tree[0]['type'] === 'department') {
                    return '<span class="tree-indent" style="display: none;"></span><ol class="sub-tree-content" style="display: none;">';
                }

                return '<span class="tree-indent"></span><ol class="sub-tree-content">';
            },
            'rootClose' => static function (array $child): ?string {
                return '</ol>';
            },
            'childOpen' => '<li>',
            'childClose' => '</li>',
            'nodeDecorator' => static function (array $node) {
                if ($node['type'] === 'corperations') {
                    return '
                    <div class="item-content scroll-item">
                        <div class="arrow-icon">
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <div class="org-icon">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div class="org-name">
                            <div class="org-text-content">' .
                            $node['name']
                            . '</div>
                        </div>
                    </div>
                    ';
                }

                if ($node['type'] === 'company') {
                    $arrayIcon = !empty($node['__children']) ? '<i class="fa-solid fa-caret-right"></i>' : '';

                    return '
                    <div class="item-content scroll-item">
                        <div class="arrow-icon">' . $arrayIcon . '</div>
                        <div class="org-icon">
                            <i class="fa-solid fa-building-user"></i>
                        </div>
                        <div class="org-name">
                            <div class="org-text-content company" type="company" id="' . $node['id'] . '">' .
                            $node['name']
                            . '</div>
                        </div>
                    </div>
                    ';
                }

                if ($node['type'] === 'department') {
                    $arrayIcon = !empty($node['__children']) ? '<i class="fa-solid fa-caret-right"></i>' : '';

                    return '
                    <div class="item-content scroll-item">
                        <div class="arrow-icon">' . $arrayIcon . '</div>
                        <div class="org-icon">
                            <i class="fa-solid fa-user-group"></i>
                        </div>
                        <div class="org-name">
                            <div class="org-text-content department" type="department" id="' . $node['id'] . '">' .
                            $node['name']
                            . '</div>
                        </div>
                    </div>
                    ';
                }
            }
        ]);

        // 2. 分页获取员工列表
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $offset = ($page - 1) * $limit;

        $empRepo = $em->getRepository(Employee::class);
        $qb = $empRepo->createQueryBuilder('e')
            ->leftJoin('e.department', 'd')
            ->leftJoin('e.position', 'p')
            ->orderBy('e.employeeNo', 'ASC');

        // 搜索功能 (可选，支持按姓名或工号搜索)
        $search = $request->query->get('q');
        if ($search) {
            $qb->andWhere('e.name LIKE :search OR e.employeeNo LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // 计算总数
        $countQb = clone $qb;
        $countQb->resetDQLPart('orderBy');
        $countQb->select('count(e.id)');
        $totalItems = $countQb->getQuery()->getSingleScalarResult();
        $totalPages = ceil($totalItems / $limit);

        // 获取当前页数据
        $employees = $qb->select('e', 'd', 'p') // 预加载部门和岗位
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        // 3. 统计数据 (从数据库获取真实数据)
        $activeCount = $empRepo->count(['status' => 'active']);
        $deptCount = $deptRepo->count(['type' => 'department']);

        // 简单的统计数据结构
        $stats = [
            'total_employee' => ['value' => $totalItems, 'change' => '+0', 'trend' => 'flat'],
            'active_employee' => ['value' => $activeCount, 'change' => '+0', 'trend' => 'flat'],
            'total_department' => ['value' => $deptCount, 'change' => '+0', 'trend' => 'flat'],
            // 平均工龄暂时使用静态数据或后续实现
            'average_tenure' => ['value' => '3.2', 'unit' => 'years', 'change' => '+0%', 'trend' => 'up'],
        ];

        return $this->render('employee/list.html.twig', [
            'tree' => $tree,
            'entities' => $employees,
            'stats' => $stats,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'limit' => $limit,
                'start' => $offset + 1,
                'end' => min($offset + $limit, $totalItems)
            ]
        ]);
    }

    #[Route('/employee/{id}', name: 'employee_show', requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function show(string $id, EntityManagerInterface $em): Response
    {
        $employee = $em->getRepository(Employee::class)->find($id);

        if (!$employee) {
            throw $this->createNotFoundException('Employee not found');
        }

        // Map Entity to View Data (adapting to the template's expectation)
        $viewData = (object)[
            // Basic Info
            'id' => $employee->getId(),
            'name' => $employee->getName(),
            'englishName' => $employee->getEnglishName(),
            'avatar' => 'https://i.pravatar.cc/300?u=' . $employee->getId(), // Placeholder
            'position' => $employee->getPosition() ? $employee->getPosition()->getName() : '',
            'employeeNo' => $employee->getEmployeeNo(),
            'status' => $employee->getStatus(), // active, inactive, etc.
            'hireDate' => $employee->getHireDate() ? $employee->getHireDate()->format('Y-m-d') : '-',
            'email' => $employee->getEmail(),
            'mobile' => $employee->getMobile(),
            
            // Org Info
            'department' => $employee->getDepartment() ? $employee->getDepartment()->getName() : '-',
            'company' => $employee->getCompany() ? $employee->getCompany()->getName() : '-',
            'manager' => (object)[
                'id' => $employee->getManager() ? $employee->getManager()->getId() : '', 
                'name' => $employee->getManager() ? $employee->getManager()->getName() : 'N/A', 
                'avatar' => 'https://i.pravatar.cc/300?u=' . ($employee->getManager() ? $employee->getManager()->getId() : 'default')
            ],
            'subordinates' => [], // Will populate below
            
            // Personal Info
            'gender' => $employee->getGender(),
            'birthDate' => $employee->getBirthDate() ? $employee->getBirthDate()->format('Y-m-d') : '-',
            'idCard' => $employee->getIdCard(),
            'address' => $employee->getAddress(),
            'emergencyContact' => $employee->getEmergencyContact(),
            'emergencyPhone' => $employee->getEmergencyPhone(),
            
            // Education Info
            'education' => $employee->getEducation(),
            'school' => $employee->getSchool(),
            'major' => $employee->getMajor(),
            'graduationDate' => $employee->getGraduationDate() ? $employee->getGraduationDate()->format('Y-m-d') : '-',
            
            // Account Info
            'username' => $employee->getUsername(),
            'lastLoginAt' => $employee->getLastLoginAt() ? $employee->getLastLoginAt()->format('Y-m-d H:i:s') : '-',
            'roles' => $employee->getRoles(),
            'isActive' => $employee->getIsActive(),
            
            // Stats (Mock for Dashboard - as per requirement to use test page implementation)
            'payout' => [
                'total' => 17877,
                'base' => 15000,
                'overtime' => 2877
            ],
            'timeWorked' => '47h 21m',
            'reimbursement' => 235.00,
            
            // Tasks (Mock)
            'activeTasks' => [
                [
                    'title' => 'Develop marketing strategy',
                    'deadline' => 'Feb 10, 2024 at 6:00 pm',
                    'status' => 'In Progress',
                    'color' => '#f97316'
                ],
                [
                    'title' => 'Re-branding oaxel (Logo, Website and Colors)',
                    'deadline' => 'Feb 10, 2024 at 6:00 pm',
                    'status' => 'Pending',
                    'color' => '#22c55e'
                ]
            ],
            
            // Activity Log (Mock)
            'activityLog' => [
                [
                    'time' => '09:30',
                    'action' => 'Clock-in',
                    'status' => 'Early',
                    'statusColor' => '#22c55e'
                ],
                [
                    'time' => '11:00 - 13:00',
                    'action' => 'Break - Lunch',
                    'status' => 'Late',
                    'statusColor' => '#ef4444'
                ],
                [
                    'time' => '23:59',
                    'action' => 'Clock-out',
                    'status' => 'GOOD',
                    'statusColor' => '#8b5cf6'
                ]
            ],
            
            // Documents (Mock)
            'documents' => [
                ['name' => 'NDA - ' . $employee->getName() . ' 2025', 'size' => '12mb', 'date' => '12 April', 'type' => 'pdf'],
                ['name' => 'Labor Contract', 'size' => '2mb', 'date' => '12 Jan', 'type' => 'pdf']
            ]
        ];

        // Populate Subordinates
        foreach ($employee->getSubordinates() as $sub) {
            $viewData->subordinates[] = (object)[
                'id' => $sub->getId(),
                'name' => $sub->getName(),
                'avatar' => 'https://i.pravatar.cc/300?u=' . $sub->getId()
            ];
        }

        return $this->render('employee/show.html.twig', [
            'employee' => $viewData
        ]);
    }
}

