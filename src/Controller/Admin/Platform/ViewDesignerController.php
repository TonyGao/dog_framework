<?php

namespace App\Controller\Admin\Platform;

use App\Entity\Platform\View;
use App\Controller\BaseController;
use App\Form\Platform\ViewFolderType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ViewDesignerController extends BaseController
{
  /**
   * 视图管理界面
   *
   * @return Response
   */
  #[Route('/admin/platform/view/index', name: 'platform_view')]
  public function index(EntityManagerInterface $em): Response
  {
    $repo = $em->getRepository(View::class);
    $views = $repo->childrenHierarchy(null, false, [
      'decorate' => true,
      'rootOpen' => static function (array $tree): ?string {
        if ([] !== $tree && 0 == $tree[0]['lvl']) {
          return '<ol class="ol-left-tree">';
        }

        if ($tree[0]['type'] === 'view') {
          return '<span class="tree-indent" style="display: none;"></span><ol class="sub-tree-content" style="display: none;">';
        }

        return '<span class="tree-indent"></span><ol class="sub-tree-content">';
      },
      'rootClose' => static function (array $child): ?string {
        return '</ol>';
      },
      'childOpen' => '<li>',
      'childClose' => '</li>',
      'nodeDecorator' => static function (array $node) use (&$controller): ?string {
        if ($node['type'] === 'root') {
          return '
            <div class="item-content scroll-item">
              <div class="arrow-icon">
                <i class="fa-solid fa-caret-down"></i>
              </div>
              <div class="org-icon">
                <i class="fa-solid fa-newspaper"></i>
              </div>
              <div class="node-name">
                <div class="tree-text-content entity-root" type="root">视图</div>
              </div>
            </div>
            ';
        }

        if ($node['type'] === 'folder') {
          $arrayIcon = !empty($node['__children']) ? '<i class="fa-solid fa-caret-down"></i>' : '';

          return '
              <div class="item-content scroll-item">
                <div class="arrow-icon">' . $arrayIcon . '</div>
                <div class="org-icon">
                  <i class="fa-regular fa-folder"></i>
                </div>
                <div class="node-name">
                  <div class="tree-text-content branch folder" type="folder" id="' . $node['id'] . '">' .
            $node['name']
            . '</div>
                </div>
              </div>
              ';
        }

        if ($node['type'] === 'view') {
          $arrayIcon = !empty($node['__children']) ? '<i class="fa-solid fa-caret-right"></i>' : '';

          return '
            <div class="item-content scroll-item">
              <div class="arrow-icon">' . $arrayIcon . '</div>
              <div class="org-icon">
                <i class="fa-solid fa-o"></i>
              </div>
              <div class="node-name">
                <div class="tree-text-content branch" type="property">' . $node['label'] . '</div>
              </div>
            </div>
            ';
        }
      }
    ]);
    return $this->render('admin/platform/view/index.html.twig', [
      'views' => $views
    ]);
  }

  /**
   * 新建文件夹的表单
   *
   * @param Request $request
   * @param EntityManagerInterface $em
   * @return Response
   */
  #[Route('/admin/platform/view/addFolder', name: 'platform_view_add_folder')]
  public function addFolder(Request $request, EntityManagerInterface $em): Response
  {
    $parentId = $request->query->get('parent');
    $view = new View();
    $view->setType('folder'); // 新建的类型为文件夹

    // 处理上级目录的逻辑
    if ($parentId) {
      $parent = $em->getRepository(View::class)->find($parentId);
      if ($parent && $parent->getType() === 'folder') {
        $view->setParent($parent);
      } else {
        return new JsonResponse(['error' => '上级目录无效或不是文件夹'], 400);
      }
    } else {
      // 如果没有父目录，创建根目录
      $root = $em->getRepository(View::class)->findOneBy(['name' => 'root']);
      if ($root === null) {
        $root = new View();
        $root->setName('root');
        $root->setLabel('Root');
        $root->setType('root');
        $em->persist($root);
        $em->flush();
        $view->setParent($root);
      } else {
        $view->setParent($root);
      }
    }

    // 创建表单并处理请求
    $form = $this->createForm(ViewFolderType::class, $view, [
      'action' => $this->generateUrl('platform_view_add_folder')
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $em->persist($view);
      $em->flush();

      $this->addFlash('success', '文件夹创建成功');
      return $this->redirectToRoute('platform_view'); // 重定向到视图管理页面
    }

    return $this->render('admin/platform/view/add_folder.html.twig', [
      'form' => $form->createView(),
    ]);
  }
}
