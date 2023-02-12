<?php

namespace App\Controller\Admin;

use App\Entity\Organization\Corporation;
use App\Entity\Organization\Company;
use App\Entity\Organization\Department;
use App\Form\Organization\CorporationFormType;
use App\Form\Organization\DepartmentType;
use App\Form\Organization\CompanyType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * 组织架构管理
 */
class OrgController extends AbstractController
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

    return $this->render('admin/org/corporation.html.twig',[
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

        $department->setName($corporation->getName())
            ->setType('集团');
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
  public function editCompany(Request $request, EntityManagerInterface $em, int $id): Response
  {
    $repo = $em->getRepository(Company::class);
    $company = $repo->findOneBy(['id'=>$id]);
    $oldName = $company->getName();

    $form = $this->createForm(CompanyType::class, $company);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $submitCompany = $form->getData();
      $em->persist($submitCompany);

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
    // $department = $repo->childrenHierarchy();
    $department = $repo->childrenHierarchy(null, false, [
      'decorate' => true,
      'rootOpen' => static function (array $tree): ?string {
        if ([] !== $tree && 0 == $tree[0]['lvl']) {
          return '<ol class="ol-left-tree">';
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
      'nodeDecorator' => static function(array $node) use (&$controller): ?string {
        if ($node['type'] === '集团') {
          return '
          <div class="item-content scroll-item">
            <div class="arrow-icon">
              <i class="fa-solid fa-caret-down"></i>
            </div>
						<div class="org-icon">
              <i class="fa-solid fa-building"></i>
						</div>
						<div class="org-name">
							<div class="org-text-content">'.
								$node['name']
							.'</div>
						</div>
					</div>
          ';
        }

        if ($node['type'] === '公司') {
          return '
          <div class="item-content scroll-item">
            <div class="arrow-icon">
              <i class="fa-solid fa-caret-down"></i>
            </div>
						<div class="org-icon">
              <i class="fa-solid fa-building-user"></i>
						</div>
						<div class="org-name">
							<div class="org-text-content company">'.
								$node['name']
							.'</div>
						</div>
					</div>
          ';
        }

        if ($node['type'] === '部门') {
          return '
          <div class="item-content scroll-item">
            <div class="arrow-icon"></div>
						<div class="org-icon">
              <i class="fa-solid fa-user-group"></i>
						</div>
						<div class="org-name">
							<div class="org-text-content department">'.
								$node['name']
							.'</div>
						</div>
					</div>
          ';
        }
      }
    ]);
    return $this->render('admin/org/department.html.twig',[
      'departmentTree' => $department
    ]);
  }

  /**
   * 新建部门表单
   */
  #[Route('/admin/org/department/new', name: 'org_department_new')]
  public function createDepartment(Request $request, EntityManagerInterface $em): Response
  {
    $department = new Department();
    $form = $this->createForm(DepartmentType::class, $department);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $department = $form->getData();
      $em->persist($department);
      $em->flush();

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
    $deparment = $repo->findOneBy(['id'=>$id]);

    $form = $this->createForm(DepartmentType::class, $deparment);
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
}
