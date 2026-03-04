<?php

namespace App\Controller\Test;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Organization\Department;
use Doctrine\ORM\EntityManagerInterface;

class TestController extends AbstractController
{

  public function __construct(
    private RequestStack $requestStack,
  ) {
  }

  #[Route('/test/tree', methods: ['GET'], name: 'test_tree')]
  public function tree(EntityManagerInterface $em): Response
  {
      $repo = $em->getRepository(Department::class);
      
      // Fetch the tree as a nested array (decorate = false)
      $tree = $repo->childrenHierarchy(null, false, [
          'decorate' => false,
          'rootOpen' => null,
          'rootClose' => null,
          'childOpen' => null,
          'childClose' => null,
          'nodeDecorator' => null
      ]);

      // Mock data for icon showcase
      $mockTree = [
          [
              'id' => 'root-1',
              'name' => '系统模型根节点',
              'type' => 'root',
              '__children' => [
                  [
                      'id' => 'ns-1',
                      'name' => '基础模块',
                      'type' => 'namespace',
                      '__children' => [
                          [
                              'id' => 'entity-1',
                              'name' => '用户实体',
                              'type' => 'entity',
                              '__children' => [
                                  [
                                      'id' => 'group-1',
                                      'name' => '基本信息',
                                      'type' => 'group',
                                      '__children' => [
                                          ['id' => 'prop-1', 'name' => '姓名', 'type' => 'property', '__children' => []],
                                          ['id' => 'prop-2', 'name' => '邮箱', 'type' => 'property', '__children' => []],
                                      ]
                                  ]
                              ]
                          ]
                      ]
                  ],
                  [
                      'id' => 'ns-2',
                      'name' => '业务模块',
                      'type' => 'namespace',
                      '__children' => []
                  ]
              ]
          ]
      ];

      // Mock data for menu showcase (draggable + checkbox)
      $mockMenuTree = [
          [
              'id' => 'menu-root',
              'name' => '系统菜单',
              'type' => 'root',
              '__children' => [
                  [
                      'id' => 'menu-1',
                      'name' => 'Dashboard',
                      'type' => 'menu',
                      '__children' => []
                  ],
                  [
                      'id' => 'menu-2',
                      'name' => '系统管理',
                      'type' => 'menu',
                      '__children' => [
                          ['id' => 'menu-2-1', 'name' => '用户管理', 'type' => 'menu', '__children' => []],
                          ['id' => 'menu-2-2', 'name' => '角色管理', 'type' => 'menu', '__children' => []],
                          ['id' => 'menu-2-3', 'name' => '菜单管理', 'type' => 'menu', '__children' => []],
                      ]
                  ],
                  [
                      'id' => 'menu-3',
                      'name' => '设置',
                      'type' => 'menu',
                      '__children' => []
                  ]
              ]
          ]
      ];

      return $this->render('test/tree.html.twig', [
          'tree' => $tree,
          'mockTree' => $mockTree,
          'mockMenuTree' => $mockMenuTree
      ]);
  }

  #[Route('/test/{element}', methods: ['GET'], name: 'element_page')]
  public function element(Request $request, $element): Response
  {
    $session = $this->requestStack->getSession();
    // $session->setId("AAA");
    $template = 'test/' . $element . '.html.twig';
    return $this->render($template);
  }

  #[Route('/', methods: ['GET'], name: 'index_page')]
  public function index(Request $request): Response
  {
    return $this->render('test/test.html.twig');
  }
}
