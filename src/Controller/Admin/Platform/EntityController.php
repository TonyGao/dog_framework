<?php

namespace App\Controller\Admin\Platform;

use App\Lib\Str;
use App\Entity\Platform\Entity;
use App\Entity\Platform\EntityProperty;
use App\Controller\BaseController;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Entity\EntityFormService;
use App\Entity\Platform\EntityPropertyGroup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Controller\Api\ApiResponse;

/**
 * 实体控制器
 */
class EntityController extends BaseController
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
              <i class="fa-solid fa-database"></i>
						</div>
						<div class="node-name">
							<div class="tree-text-content">模型</div>
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
              <i class="fa-solid fa-table"></i>
						</div>
						<div class="node-name">
							<div class="tree-text-content branch" type="entity" id="'. $node['token'] .'">' .
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
              <i class="fa-solid fa-o"></i>
						</div>
						<div class="node-name">
							<div class="tree-text-content branch" type="branch">' .
            $node['label']
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

  /**
   * 实体数据表格
   */
  #[Route('/admin/platform/entity/itemtable', name: 'platform_entity_itemtable')]
  public function item(Request $request, EntityManagerInterface $em): Response
  {
    $repo = $em->getRepository(Entity::class);
    $token = $request->query->get('token');
    $entity = $repo->findOneBy(['token' => $token]);
    $repoProperty = $em->getRepository(EntityProperty::class);
    $entityProperties = $repoProperty->findBy(
      ['entity' => $entity],
      ['createdAt' => 'ASC']
    );

    $arr = array();
    foreach($entityProperties as $entity) {
      $et = new \stdClass();
      $et->name = $entity->getPropertyName();
      $et->comment = $entity->getComment();
      $et->type = $entity->getType();
      $et->length = $entity->getLength();
      $et->entity = $entity->getEntity();
      $et->group = $entity->getGroup()->getLabel();
      $arr[] = $et;
    }

    $button = '<div class="toolbar-box">
      <div class="toolbar-wrap">
        <div class="toolbar-content">
          <button class="create btn outline primary medium mini round icon" token="'. $token .'"><i class="fa-regular fa-square-plus"></i>添加自定义字段</button>
          <button class="group btn outline primary medium mini round icon" token="'. $token .'"><i class="fa-regular fa-object-group"></i>字段分组管理</button>
        </div>
      </div>
    </div>';

    return $this->render('ui/table.html.twig', [
      'entities' => $arr,
      'toolbar' => $button,
    ]);
  }

  #[Route(
    '/admin/platform/entity/addFieldDrawer', 
    name: 'platform_entity_addFieldDrawer', 
    methods: ['POST']
  )]
  public function addFieldDrawer(Request $request, EntityFormService $formService): Response
  {
    $payload = $request->toArray();
    $entityToken = $payload['token'];

    $form = $formService->addField($entityToken, null, true);

    return $this->render('ui/drawer/drawer.html.twig', [
      'id' => $entityToken,
      'drawerTitle' => '添加字段',
      'width' => '840',
      'drawerContent' => $form['form'],
      'drawerTitleAddon' => $form['additional'],
      //'drawerTitleAddonTwig' => $this->_getDrawTitleAddon(),
    ]);
  }

  #[Route(
    '/admin/platform/entity/addField',
    name: 'platform_entity_addField',
    methods: ['POST']
  )]
  public function addField(Request $request, EntityFormService $formService)
  {
    $payload = $request->getPayload();
    $token = $payload->get('token');
    $choosedGroup = $payload->get('choosedGroup');
    $htmlContent = $formService->addField($token, $choosedGroup, false);
    $response = new Response($htmlContent['form']);
    $response->headers->set('Content-Type', 'text/html');
    return $response;
  }
}
