<?php

namespace App\Controller\Api\Admin\Organization;

use App\Entity\Organization\Company;
use App\Entity\Organization\Department;
use App\Entity\Organization\Position;
use App\Entity\Platform\Entity;
use App\Service\Platform\DataGridService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\Api\ApiResponse;
use App\Entity\Organization\Corporation;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class OrgApiController extends AbstractController
{
  #[Route('/api/admin/org/company/list', name: 'api_org_company_list')]
  public function listCompany(Request $request, EntityManagerInterface $em): JsonResponse
  {
    $repo = $em->getRepository(Company::class);
    $company = null;
    $company = $repo->findAll();
    $data = ['status' => 'ok'];

    return new JsonResponse($data);
  }

  #[Route(
    '/api/admin/org/company/batchcreate',
    name: 'api_org_company_batchcreate',
    methods: ['POST']
  )]
  public function batchCreateCompany(Request $request, EntityManagerInterface $em): JsonResponse
  {
    //Todo 暂时只支持单集团模式
    $corporationRepo = $em->getRepository(Corporation::class);
    $corporation = $corporationRepo->findOneBy([]);

    $payload = $request->toArray();
    $repo = $em->getRepository(Company::class);
    $root = $repo->findOneBy(['lvl' => 0]);
    foreach ($payload['company'] as $company) {
      $com = new Company();
      $com->setName($company)
        ->setAlias($company)
        ->setOwnerCompany($com)
        ->setParent($root);
      $em->persist($com);
      $em->flush();

      $depRepo = $em->getRepository(Department::class);
      $depRoot = $depRepo->findOneBy(['lvl' => 0]);
      $department = new Department();
      $department->setName($company)
        ->setAlias($company)
        ->setCompany($com)
        ->setType('company')
        ->setParent($depRoot)
        ->setOwnerCompany($com);
      $em->persist($department);
      $em->flush();
    }
    $data = ['status' => 'ok'];

    return new JsonResponse($data);
  }

  #[Route(
    '/api/admin/org/department/searchByKey',
    name: 'api_org_department_searchByKey',
    methods: ['POST']
  )]
  public function searchByKey(
    Request $request,
    EntityManagerInterface $em,
    SerializerInterface $serializer
  ): ApiResponse 
  {
    $payload = $request->toArray();
    $repo = $em->getRepository(Department::class);
    $data = $repo->createQueryBuilder('d')
      ->where('d.alias LIKE :key')
      ->orWhere('d.name LIKE :key')
      ->andWhere('d.state = true')
      ->andWhere('d.type = :type')
      ->setParameter('key', '%' . $payload['key'] . '%')
      ->setParameter('type', 'department')
      ->getQuery()
      ->getResult();

    foreach ($data as $item) {
      $name = '';
      $path = $repo->getPath($item);
      // 如果父级是'department'类型，那就不用再继续拼接非部门(集团、公司)的类型
      $type = $item?->getParent()?->getType();
      foreach ($path as $pathItem) {
        // 如果是集团、公司类型就用简称拼写，如果是部门就用名称拼写
        $itemType = $pathItem->getType();
        if ($itemType == 'corperations' || $itemType == 'company') {
          $name .= $pathItem->getAlias() . '/';
        }
        if ($pathItem->getType() == 'department') {
          $name .= $pathItem->getName() . '/';
        }
      }
      $name = rtrim($name, "/");
      $item->setDisplayName($name);
    }

    $content = $serializer->serialize($data, 'json', ['groups' => ['api']]);
    return ApiResponse::success($content, '200', 'success');
  }

  #[Route('/api/admin/org/position/list', name: 'api_org_position_list', methods: ['GET'])]
  public function positionList(Request $request, DataGridService $dataGridService): JsonResponse
  {
    $page = $request->query->getInt('page', 1);
    $pageSize = $request->query->getInt('pageSize', 20);

    // 使用新的 DataGridService 获取表格数据和配置
    $result = $dataGridService->getTableData(
      'App\\Entity\\Organization\\Position',
      $page,
      $pageSize
    );

    return $this->json($result);
  }
}
