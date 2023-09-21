<?php

namespace App\Controller\Admin\Platform;

use App\Entity\Platform\EntityPropertyGroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * 实体控制器
 */
class EntityController extends AbstractController
{
  /**
   * 实体管理首页
   */
  #[Route('/admin/platform/entity/index', name: 'platform_entity')]
  public function index(EntityManagerInterface $em): Response
  {
    $repo = $em->getRepository(EntityPropertyGroup::class);
    $entity = $repo->childrenHierarchy(null, false, [
      'decorate' => true,
      'rootOpen' => static function (array $tree): ?string {
        if ([] !== $tree && 0 == $tree[0]['lvl']) {
          return '<ol class="ol-left-tree">';
        }

        if ($tree[0]['type'] === 'group' || $tree[0]['type'] === 'property') {
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
              <i class="fa-solid fa-building"></i>
						</div>
						<div class="org-name">
							<div class="org-text-content">模型</div>
						</div>
					</div>
          ';
        }

        if ($node['type'] === 'entity') {
          $arrayIcon = !empty($node['__children']) ? '<i class="fa-solid fa-caret-right"></i>' : '';

          return '
          <div class="item-content scroll-item">
            <div class="arrow-icon">' . $arrayIcon . '</div>
						<div class="org-icon">
              <i class="fa-solid fa-building-user"></i>
						</div>
						<div class="org-name">
							<div class="org-text-content company" type="company">' .
            $node['name']
            . '</div>
						</div>
					</div>
          ';
        }

        if ($node['type'] === 'group' || $node['type'] === 'property') {
          $arrayIcon = !empty($node['__children']) ? '<i class="fa-solid fa-caret-right"></i>' : '';

          return '
          <div class="item-content scroll-item">
            <div class="arrow-icon">' . $arrayIcon . '</div>
						<div class="org-icon">
              <i class="fa-solid fa-user-group"></i>
						</div>
						<div class="org-name">
							<div class="org-text-content department" type="department">' .
            $node['name']
            . '</div>
						</div>
					</div>
          ';
        }
      }
    ]);
    return $this->render('admin/platform/entity/index.html.twig',[
      'entity' => $entity
    ]);
  }
}
