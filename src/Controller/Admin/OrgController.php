<?php

namespace App\Controller\Admin;

use App\Entity\Organization\Corporation;
use App\Entity\Organization\Company;
use App\Form\Organization\CorporationFormType;
use App\Form\Organization\CompanyType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class OrgController extends AbstractController
{
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
        'companies' => $comTree[0]['__children'],
    ]);
  }

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

        // 如果是首次创建集团信息，初始化相关的根公司
        if ($isFirstTime) {
          $company = new Company();
          $company->setName($corporation->getName());
          if ($corporation->getAlias() != null) {
            $company->setAlias($corporation->getAlias());
          }
          $em->persist($company);
        }
        $em->flush();

        return $this->redirectToRoute('org_corporation');
    }

    return $this->render('admin/org/corporationEdit.html.twig', [
        'form' => $form->createView(),
    ]);
  }

  #[Route('/admin/org/company/edit/{id}', name: 'org_company_edit')]
  public function editCompany(Request $request, EntityManagerInterface $em, int $id): Response
  {
    $repo = $em->getRepository(Company::class);
    $company = $repo->findOneBy(['id'=>$id]);

    $form = $this->createForm(CompanyType::class, $company);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $submitCompany = $form->getData();
      $em->persist($submitCompany);
      $em->flush();

      return $this->redirectToRoute('org_corporation');
    }

    return $this->render('admin/org/companyEdit.html.twig', [
      'form' => $form->createView(),
    ]);
  }
}
