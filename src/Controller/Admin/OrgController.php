<?php

namespace App\Controller\Admin;

use Symfony\Component\Uid\Uuid;
use App\Controller\BaseController;
use App\Entity\Organization\Company;
use App\Form\Organization\CompanyType;
use App\Entity\Organization\Department;
use App\Entity\Organization\Corporation;
use App\Entity\Organization\Position;
use App\Entity\Organization\PositionLevel;
use App\Entity\Platform\Entity;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Organization\OrgDepartmentType;
use App\Form\Organization\PositionType;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Organization\CorporationFormType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 组织架构管理
 */
class OrgController extends BaseController
{

  /**
   * 公司架构首页
   */
  #[Route('/admin/org/corporation', name: 'org_corporation')]
  public function corporation(Request $request, EntityManagerInterface $em): Response
  {
    $repo = $em->getRepository(Corporation::class);
    $corporation = null;
    $corporationArr = $repo->findAll();
    if ($corporationArr !== []) {
      $corporation = $corporationArr[0];
    }

    $comRepo = $em->getRepository(Company::class);
    $comTree = $comRepo->childrenHierarchy();

    return $this->render('admin/org/corporation.html.twig', [
      'corporation' => $corporation,
      'companies' => $comTree !== [] ? $comTree[0]['__children'] : null,
    ]);
  }

  /**
   * 集团编辑页面
   */
  #[Route('/admin/org/corporation/edit', name: 'org_corporation_edit')]
  public function createCorporation(Request $request, EntityManagerInterface $em): Response
  {
    $repo = $em->getRepository(Corporation::class);
    $corporationArr = $repo->findAll();
    $corporation = new Corporation();
    $isFirstTime = true;
    if ($corporationArr !== []) {
      $corporation = $corporationArr[0];
      $isFirstTime = false;
    }

    $form = $this->createForm(CorporationFormType::class, $corporation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $corporation = $form->getData();
      $em->persist($corporation);
      $em->flush();

      // 如果是首次创建集团信息，初始化相关的根公司，再初始化相关的根部门
      if ($isFirstTime) {
        $company = new Company();
        $department = new Department();
      }

      // 如果不是第一次更新集团信息，同时更新根公司和根部门
      if (!$isFirstTime) {
        $repo = $em->getRepository(Company::class);
        $company = $repo->findOneBy(['lvl' => 0]);

        $depRepo = $em->getRepository(Department::class);
        $department = $depRepo->findOneBy(['lvl' => 0]);
      }

      $company->setName($corporation->getName());
      if ($corporation->getAlias() != null) {
        $company->setAlias($corporation->getAlias());
      }
      $em->persist($company);
      $em->flush();

      $department->setName($corporation->getName())
        ->setType('corperations')
        ->setPath($corporation->getName());
      if ($corporation->getAlias() != null) {
        $department->setAlias($corporation->getAlias());
      }
      $em->persist($department);
      $em->flush();

      return $this->redirectToRoute('org_corporation');
    }

    return $this->render('admin/org/corporationEdit.html.twig', [
      'form' => $form->createView(),
    ]);
  }

  /**
   * 公司编辑页面
   */
  #[Route('/admin/org/company/edit/{id}', name: 'org_company_edit')]
  public function editCompany(Request $request, EntityManagerInterface $em, string $id): Response
  {
    $repo = $em->getRepository(Company::class);
    $company = $repo->findOneBy(['id' => $id]);
    $oldName = $company->getName();

    $form = $this->createForm(CompanyType::class, $company);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $submitCompany = $form->getData();
      $em->persist($submitCompany);
      $em->flush();

      $depRepo = $em->getRepository(Department::class);
      $department = $depRepo->findOneBy(['name' => $oldName]);
      $department->setName($submitCompany->getName())
        ->setAlias($submitCompany->getAlias());
      $em->persist($department);
      $em->flush();

      return $this->redirectToRoute('org_corporation');
    }

    return $this->render('admin/org/companyEdit.html.twig', [
      'form' => $form->createView(),
    ]);
  }

  /**
   * 组织架构-部门管理
   */
  #[Route('/admin/org/department', name: 'org_department')]
  public function department(Request $request, EntityManagerInterface $em): Response
  {
    $repo = $em->getRepository(Department::class);
    $department = $repo->childrenHierarchy(null, false, [
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
        // if ([] !== $child && 0 == $child[0]['lvl']) {
        //   return '</ol>';
        // }

        return '</ol>';
      },
      'childOpen' => '<li>',
      'childClose' => '</li>',
      'nodeDecorator' => static function (array $node) use (&$controller) {
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
							<div class="org-text-content department" type="department" path="' . $node['path'] . '" id="' . $node['id'] . '">' .
            $node['name']
            . '</div>
						</div>
					</div>
          ';
        }
      }
    ]);
    return $this->render('admin/org/department.html.twig', [
      'departmentTree' => $department
    ]);
  }

  // private function buildDepartmentPath(array $node, array $repo): string
  // {
  //   // 递归构建路径，如果有父节点则加上父节点的路径
  //   if (!empty($node['parent'])) {
  //     $parentNode = $repo->find($node['parent']['id']);
  //     return $this->buildDepartmentPath($parentNode, $repo) . '/' . $node['name'];
  //   }

  //   // 如果没有父节点，则返回当前节点名称
  //   return $node['name'];
  // }

  /**
   * 组织架构-部门选择器（单部门选择）
   */
  #[Route('/admin/org/departemnt/singleSelect', name: 'org_deparment_single_select')]
  public function singleSelectDepartment(Request $request, EntityManagerInterface $em): Response
  {
    $repo = $em->getRepository(Department::class);
    $departmentInputId = Uuid::v1();
    $departmentSingleTree = $repo->childrenHierarchy(null, false, [
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
        // if ([] !== $child && 0 == $child[0]['lvl']) {
        //   return '</ol>';
        // }

        return '</ol>';
      },
      'childOpen' => '<li>',
      'childClose' => '</li>',
      'nodeDecorator' => static function (array $node) use (&$controller, $departmentInputId) {
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
							<div class="org-text-content company" type="company">' .
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
            <span class="department-select-line">
              <label class="ef-radio" style="padding-right: 5px;" radioId="' . $departmentInputId . '">
                <input type="radio" class="ef-radio-target" value="A">
                <span class="ef-icon-hover ef-radio-icon-hover">
                  <span class="ef-radio-icon"></span>
                </span>
              </label>
              <div class="org-icon">
                <i class="fa-solid fa-user-group"></i>
              </div>
              <div class="org-name">
                <div class="org-text-content department" type="department" path="' . $node['path'] . '" id="' . $node['id'] . '">' .
            $node['name']
            . '</div>
              </div>
            </span>
					</div>
          ';
        }
      }
    ]);

    return $this->render('admin/org/department/singleSelect.html.twig', [
      'departmentSingleTree' => $departmentSingleTree
    ]);
  }

  /**
   * 新建部门表单
   */
  #[Route('/admin/org/department/new', name: 'org_department_new')]
  public function createDepartment(Request $request, EntityManagerInterface $em): Response
  {
    // 从 GET 请求中获取参数
    $parentId = $request->query->get('parent');

    $department = new Department();

    // 如果获取到部门 ID，查找对应的上级部门
    if ($parentId) {
      $parentDepartment = $em->getRepository(Department::class)->find($parentId);
      if ($parentDepartment) {
        if ($parentDepartment->getType() === 'department') {
          $department->setParent($parentDepartment);
        }
        // 预填表单中的上级部门和公司字段
        $department->setCompany($parentDepartment->getCompany());
      }
    }

    $form = $this->createForm(OrgDepartmentType::class, $department, [
      'action' => $this->generateUrl('org_department_new')
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $departmentPost = $form->getData();
      // 当新增部门里的类型为部门，并且上级部门为空时，将所属公司同名的部门找出作为上级部门
      // 即这个部门是在个一级部门
      if ($departmentPost->getType() == 'department' && $departmentPost->getParent() == null) {
        $company = $departmentPost->getCompany()->getName();
        $repo = $em->getRepository(Department::class);
        $parent = $repo->findOneBy(['name' => $company]);
        $departmentPost->setParent($parent);
      }

      // 设置 path 字段
      $pathComponents = [];

      // 如果有上级部门，添加上级部门的路径
      if ($departmentPost->getParent()) {
        $pathComponents[] = $departmentPost->getParent()->getPath();
      }

      // 添加当前部门的名称
      $pathComponents[] = $departmentPost->getName();

      // 生成完整路径
      $departmentPost->setPath(implode('/', $pathComponents));

      $em->persist($departmentPost);
      $em->flush();

      // 添加 flash 消息，通知前端缓存需要清理
      $this->addFlash('org.singleDepartment', 'clear');
      return $this->redirectToRoute('org_department');
    }

    return $this->render('admin/org/departmentNew.html.twig', [
      'form' => $form->createView(),
    ]);
  }

  /**
   * 返回部门表单
   */
  #[Route('/admin/org/department/edit/{id}', name: 'org_department_edit')]
  public function departmentForm(Request $request, EntityManagerInterface $em, Department $department): Response
  {
    $form = $this->createForm(OrgDepartmentType::class, $department, [
      'action' => $this->generateUrl('org_department_edit', ['id' => $department->getId()])
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $submitDepartment = $form->getData();
      $em->persist($submitDepartment);
      $em->flush();

      return $this->redirectToRoute('org_department');
    }

    return $this->render('admin/platform/form_render.html.twig', [
      'form' => $form->createView(),
    ]);
  }





  /**
   * 岗位管理列表页
   */
  #[Route('/admin/org/position', name: 'org_position')]
  public function positionList(Request $request, EntityManagerInterface $em): Response
  {
    // 初始页面加载，只返回空数据和配置
    $positionLevels = $em->getRepository(PositionLevel::class)->findAll();

    // 定义表格列配置
    $columns = [
      ['field' => 'name', 'label' => '岗位名称'],
      ['field' => 'code', 'label' => '岗位编码'],
      ['field' => 'department', 'label' => '所属部门'],
      ['field' => 'level', 'label' => '岗位级别'],
      ['field' => 'parent', 'label' => '上级岗位'],
      ['field' => 'headcount', 'label' => '编制人数'],
      ['field' => 'state', 'label' => '状态'],
    ];

    return $this->render('admin/org/position/index.html.twig', [
      'tableData' => [], // 初始为空，通过AJAX加载
      'columns' => $columns,
      'positionLevels' => $positionLevels
    ]);
  }

  /**
   * 新建岗位
   */
  #[Route('/admin/org/position/new', name: 'org_position_new')]
  public function createPosition(Request $request, EntityManagerInterface $em): Response
  {
    $position = new Position();

    // 从请求中获取部门ID参数
    $departmentId = $request->query->get('department');
    if ($departmentId) {
      $department = $em->getRepository(Department::class)->find($departmentId);
      if ($department) {
        $position->setDepartment($department);
      }
    }

    // 从请求中获取上级岗位ID参数
    $parentId = $request->query->get('parent');
    if ($parentId) {
      $parent = $em->getRepository(Position::class)->find($parentId);
      if ($parent) {
        $position->setParent($parent);
        // 如果有上级岗位，默认使用上级岗位的部门
        if (!$position->getDepartment() && $parent->getDepartment()) {
          $position->setDepartment($parent->getDepartment());
        }
      }
    }

    $form = $this->createForm(PositionType::class, $position, [
      'action' => $this->generateUrl('org_position_new')
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $position = $form->getData();
      $em->persist($position);
      $em->flush();

      $this->addFlash('success', '岗位创建成功');
      return $this->redirectToRoute('org_position');
    }

    return $this->render('admin/org/position/form.html.twig', [
      'form' => $form->createView(),
      'title' => '新建岗位'
    ]);
  }

  /**
   * 编辑岗位
   */
  #[Route('/admin/org/position/edit/{id}', name: 'org_position_edit')]
  public function editPosition(Request $request, EntityManagerInterface $em, string $id): Response
  {
    $position = $em->getRepository(Position::class)->find($id);

    if (!$position) {
      throw $this->createNotFoundException('岗位不存在');
    }

    $form = $this->createForm(PositionType::class, $position, [
      'action' => $this->generateUrl('org_position_edit', ['id' => $id])
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $position = $form->getData();
      $em->persist($position);
      $em->flush();

      $this->addFlash('success', '岗位更新成功');
      return $this->redirectToRoute('org_position');
    }

    return $this->render('admin/org/position/form.html.twig', [
      'form' => $form->createView(),
      'title' => '编辑岗位',
      'position' => $position
    ]);
  }

  /**
   * 删除岗位
   */
  #[Route('/admin/org/position/delete/{id}', name: 'org_position_delete', methods: ['POST'])]
  public function deletePosition(Request $request, EntityManagerInterface $em, string $id): Response
  {
    $position = $em->getRepository(Position::class)->find($id);

    if (!$position) {
      throw $this->createNotFoundException('岗位不存在');
    }

    // 检查是否有下级岗位
    $hasChildren = $em->getRepository(Position::class)->findBy(['parent' => $position]);
    if (count($hasChildren) > 0) {
      $this->addFlash('error', '该岗位存在下级岗位，无法删除');
      return $this->redirectToRoute('org_position');
    }

    // 检查是否有员工关联
    $hasEmployees = $em->getRepository('App\Entity\Organization\Employee')->findBy(['position' => $position]);
    if (count($hasEmployees) > 0) {
      $this->addFlash('error', '该岗位已有员工关联，无法删除');
      return $this->redirectToRoute('org_position');
    }

    $em->remove($position);
    $em->flush();

    $this->addFlash('success', '岗位删除成功');
    return $this->redirectToRoute('org_position');
  }

  /**
   * 岗位详情
   */
  #[Route('/admin/org/position/view/{id}', name: 'org_position_view')]
  public function viewPosition(Request $request, EntityManagerInterface $em, string $id): Response
  {
    $position = $em->getRepository(Position::class)->find($id);

    if (!$position) {
      throw $this->createNotFoundException('岗位不存在');
    }

    // 获取该岗位下的员工
    $employees = $em->getRepository('App\Entity\Organization\Employee')->findBy(['position' => $position]);

    return $this->render('admin/org/position/view.html.twig', [
      'position' => $position,
      'employees' => $employees
    ]);
  }

  /**
   * 岗位级别管理
   */
  #[Route('/admin/org/position/level', name: 'org_position_level')]
  public function positionLevelList(Request $request, EntityManagerInterface $em): Response
  {
    $positionLevels = $em->getRepository(PositionLevel::class)->findBy([], ['levelOrder' => 'ASC']);

    // 准备表格数据
    $tableData = [];
    foreach ($positionLevels as $level) {
      $tableData[] = [
        'id' => $level->getId(),
        'name' => $level->getName(),
        'code' => $level->getCode(),
        'levelOrder' => $level->getLevelOrder(),
        'salaryRange' => ($level->getSalaryMin() ? $level->getSalaryMin() : '0') . ' - ' . ($level->getSalaryMax() ? $level->getSalaryMax() : '0'),
        'state' => $level->getState() ? '启用' : '停用',
      ];
    }

    // 定义表格列配置
    $columns = [
      ['field' => 'name', 'label' => '级别名称'],
      ['field' => 'code', 'label' => '级别编码'],
      ['field' => 'levelOrder', 'label' => '级别序号'],
      ['field' => 'salaryRange', 'label' => '薪资范围'],
      ['field' => 'state', 'label' => '状态'],
    ];

    return $this->render('admin/org/position/level_index.html.twig', [
      'tableData' => $tableData,
      'columns' => $columns
    ]);
  }

  /**
   * 用来返回岗位选择器弹窗的html
   */
  #[Route('/admin/org/position/modal', name: 'api_org_position_modal', methods: ['POST'])]
  public function positionModal(Request $request, EntityManagerInterface $em)
  {
    $payload = $request->toArray();
    $type = $payload['positionType'] ?? 'single';
    $positionInputId = $payload['positionInputId'] ?? Uuid::v1();

    $positions = $em->getRepository(Position::class)->findBy(['state' => true]);

    if ($type == 'single') {
      return $this->render('admin/org/position/position_modal.html.twig', [
        'positionInputId' => $positionInputId,
        'positions' => $positions
      ]);
    }

    return $this->render('admin/org/position/position_multi_modal.html.twig', [
      'positionInputId' => $positionInputId,
      'positions' => $positions
    ]);
  }

  /**
   * 用来返回部门弹窗的html
   */
  #[Route('/admin/org/department/modal', name: 'api_org_department_modal', methods: ['POST'])]
  public function departmentModal(Request $request, EntityManagerInterface $em)
  {
    $payload = $request->toArray();
    $type = $payload['departmentType'];
    $departmentInputId = $payload['departmentInputId'];

    if ($type == 'single') {
      return $this->render('admin/org/department/department_modal.html.twig', [
        'departmentInputId' => $departmentInputId
      ]);
    }
  }

  /**
   * 返回岗位查看/编辑的drawer HTML
   */
  #[Route('/admin/org/position/drawer', name: 'api_org_position_drawer', methods: ['POST'])]
  public function positionDrawer(Request $request, EntityManagerInterface $em): Response
  {
    $payload = $request->toArray();
    $positionId = $payload['positionId'] ?? null;
    $action = $payload['action'] ?? 'view'; // view 或 edit
    
    if (!$positionId) {
      throw $this->createNotFoundException('岗位ID不能为空');
    }
    
    $position = $em->getRepository(Position::class)->find($positionId);
    
    if (!$position) {
      throw $this->createNotFoundException('岗位不存在');
    }
    
    // 获取该岗位下的员工
    $employees = $em->getRepository('App\Entity\Organization\Employee')->findBy(['position' => $position]);
    
    if ($action === 'edit') {
      $form = $this->createForm(PositionType::class, $position, [
        'action' => $this->generateUrl('org_position_edit', ['id' => $positionId])
      ]);
      
      return $this->render('admin/org/position/edit_drawer.html.twig', [
        'position' => $position,
        'form' => $form->createView(),
        'employees' => $employees,
        'drawerId' => 'position-drawer-' . $positionId
      ]);
    }
    
    return $this->render('admin/org/position/view_drawer.html.twig', [
      'position' => $position,
      'employees' => $employees,
      'drawerId' => 'position-drawer-' . $positionId
    ]);
  }
}
