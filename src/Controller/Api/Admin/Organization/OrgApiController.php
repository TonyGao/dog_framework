<?php

namespace App\Controller\Api\Admin\Organization;

use App\Entity\Organization\Company;
use App\Entity\Organization\Department;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\Api\ApiResponse;
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
    $payload = $request->toArray();
    $repo = $em->getRepository(Company::class);
    $root = $repo->findOneBy(['lvl' => 0]);
    foreach ($payload['company'] as $company) {
      $com = new Company();
      $com->setName($company)
        ->setAlias($company)
        ->setParent($root);

      $depRepo = $em->getRepository(Department::class);
      $depRoot = $depRepo->findOneBy(['lvl' => 0]);
      $department = new Department();
      $department->setName($company)
        ->setAlias($company)
        ->setType('company')
        ->setParent($depRoot);
      $em->persist($com);
      $em->persist($department);
    }
    $em->flush();
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
  ): ApiResponse {
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
      foreach ($path as $pathItem) {
        // 如果是集团、公司类型就用简称拼写，如果是部门就用名称拼写
        if ($pathItem->getType() != 'department') {
          $name .= $pathItem->getAlias() . '/';
        } else {
          $name .= $pathItem->getName() . '/';
        }
      }
      $name = rtrim($name, "/");
      $item->setName($name);
    }

    $content = $serializer->serialize($data, 'json', ['groups' => ['api']]);
    return ApiResponse::success($content, '200', 'success');
  }
}
