<?php

namespace App\Controller\Admin;

use Symfony\Component\Uid\Uuid;
use App\Controller\BaseController;
use App\Entity\Organization\Company;
use App\Form\Organization\CompanyType;
use App\Entity\Organization\Department;
use App\Entity\Organization\Corporation;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Organization\OrgDepartmentType;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Organization\CorporationFormType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
      'nodeDecorator' => static function (array $node) use (&$controller): ?string {
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
							<div class="org-text-content company" type="company" id="'.$node['id'].'">' .
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
							<div class="org-text-content department" type="department" path="'. $node['path'] .'" id="'. $node['id'] .'">' .
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

  private function buildDepartmentPath(array $node, array $repo): string
  {
    // 递归构建路径，如果有父节点则加上父节点的路径
    if (!empty($node['parent'])) {
      $parentNode = $repo->find($node['parent']['id']);
      return $this->buildDepartmentPath($parentNode, $repo) . '/' . $node['name'];
    }

    // 如果没有父节点，则返回当前节点名称
    return $node['name'];
  }

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
      'nodeDecorator' => static function (array $node) use (&$controller, $departmentInputId): ?string {
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
                <div class="org-text-content department" type="department" path="'. $node['path'] .'" id="'. $node['id'] .'">' .
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
  public function departmentForm(Request $request, EntityManagerInterface $em, int $id): Response
  {
    $repo = $em->getRepository(Department::class);
    $deparment = $repo->findOneBy(['id' => $id]);

    $form = $this->createForm(OrgDepartmentType::class, $deparment);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $submitDepartment = $form->getData();
      $em->persist($submitDepartment);
      $em->flush();

      return $this->redirectToRoute('org_department');
    }

    return $this->render('admin/org/departmentEdit.html.twig', [
      'form' => $form->createView(),
    ]);
  }

    /**
   * 用来返回部门弹窗的html
   */
  #[Route('/admin/org/department/modal', name: 'api_org_department_modal', methods: ['POST'])]
  public function departmentModal(Request $request, EntityManagerInterface $em): Response
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
}
