<?php

namespace App\Controller\Api\Admin\Organization;

use App\Entity\Organization\Company;
use App\Entity\Organization\Department;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class OrgApiController extends AbstractController
{
  #[Route('/api/admin/org/company/list', name: 'api_org_company_list')]
  public function listCompany(Request $request, EntityManagerInterface $em): JsonResponse
  {
    $repo = $em->getRepository(Company::class);
    $company = null;
    $company = $repo->findAll();
    $data = ['status'=>'ok'];

    return new JsonResponse($data);
  }

  #[Route('/api/admin/org/company/batchcreate',
    name: 'api_org_company_batchcreate',
    methods: ['POST']
  )]
  public function batchCreateCompany(Request $request, EntityManagerInterface $em): JsonResponse
  {
    $payload = $request->toArray();
    $repo = $em->getRepository(Company::class);
    $root = $repo->findOneBy(['lvl' => 0]);
    foreach($payload['company'] as $company) {
      $com = new Company();
      $com->setName($company)
        ->setAlias($company)
        ->setParent($root);

      $depRepo = $em->getRepository(Department::class);
      $depRoot = $depRepo->findOneBy(['lvl' => 0]);
      $department = new Department();
      $department->setName($company)
          ->setAlias($company)
          ->setType('公司')
          ->setParent($depRoot);
      $em->persist($com);
      $em->persist($department);
    }
    $em->flush();
    $data = ['status'=>'ok'];

    return new JsonResponse($data);
  }
}
