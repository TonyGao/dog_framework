<?php

namespace App\Controller\Admin;

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
    return $this->render('admin/org/corporation.html.twig');
  }
}