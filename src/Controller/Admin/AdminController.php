<?php

namespace App\Controller\Admin;

use App\Entity\Platform\Menu;
use App\Controller\BaseController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends BaseController
{
  #[Route('/admin/index', name: 'admin_index')]
  public function index(Request $request, EntityManagerInterface $em): Response
  {
    $repo = $em->getRepository(Menu::class);
    $root = $repo->childrenHierarchy();
    $root !== [] ? $menus = $root[0]['__children'] : $menus = [];
    return $this->render('admin/index.html.twig', [
      'menus' => $menus
    ]);
  }
}
