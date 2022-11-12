<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
  #[Route('/admin/index', name: 'admin_index')]
  public function index(Request $request, EntityManagerInterface $em): Response
  {
    $repo = $em->getRepository(Menu::class);
    $menus = $repo->childrenHierarchy()[0]['__children'];
    return $this->render('admin/index.html.twig', [
      'menus' => $menus
    ]);
  }
}