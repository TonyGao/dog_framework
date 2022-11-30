<?php

namespace App\Controller\Admin;

use App\Entity\Organization\Corporation;
use App\Form\CorporationType;
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
    $corporation = $repo->findAll();
    return $this->render('admin/org/corporation.html.twig',[
        'corporation' => $corporation,
    ]);
  }

  #[Route('/admin/org/corporation/edit', name: 'org_corporation_edit')]
  public function createCorporation(Request $request, EntityManagerInterface $em): Response
  {
    $corporation = new Corporation();
    $form = $this->createForm(CorporationType::class, $corporation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $corporation = $form->getData();
        $em->persist($corporation);
        $em->flush();

        return $this->redirectToRoute('org_corporation');
    }

    return $this->render('admin/org/corporationEdit.html.twig', [
        'form' => $form->createView(),
    ]);
  }
}
