<?php

namespace App\Service\Entity;

use App\Lib\Str;
use App\Entity\Platform\EntityPropertyGroup;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

class EntityFormService
{
  private $formFactory;
  private $twig;
  private $em;

  public function __construct(FormFactoryInterface $formFactory, Environment $twig, EntityManagerInterface $em)
  {
    $this->formFactory = $formFactory;
    $this->twig = $twig;
    $this->em = $em;
  }

  public function createFormBuilder($data = null, array $options = [])
  {
    // 使用注入的FormFactoryInterface服务创建表单构建器
    return $this->formFactory->createBuilder(FormType::class, $data, $options);
  }

  /**
   * 添加字段的表单字段，其中Group是通过查询EntityPropertyGroup动态获取的这个Entity的分组
   * 每个Entity都有各自的Group
   */
  public function getFieldView($entityToken, $choosedGroup, $init = true)
  {
    $groupRepo = $this->em->getRepository(EntityPropertyGroup::class);
    $entity = $groupRepo->findOneBy(['token' => $entityToken]);
    $group = $groupRepo->getChildren($entity, true, 'lft', 'asc');
    $groupArr = [];
    $defaultValue = '';
    foreach ($group as $key => $g) {
      $id = $g->getId();
      $groupArr[$g->getLabel()] = $id;

      /**
       * 如果分组为null，则采用默认分组，否则采用传入的分组id
       */
      if ($choosedGroup == null) {
        if ($g->getIsDefault()) {
          $defaultValue = $id;
        }
      }
    }

    if ($choosedGroup !== null) {
      $defaultValue = $choosedGroup;
    }

    $formBuilder = $this->createFormBuilder();
    $formBuilder
      ->add('fieldComment', TextType::class, [
        'attr' => [
          'class' => 'fieldComment',
          'id' => Str::generateFieldToken(),
          'name' => 'fieldComment'.Str::generateFieldToken(),
        ]
      ])
      ->add('fieldName', TextType::class, [
        'attr' => [
          'class' => 'fieldName',
          'id' => Str::generateFieldToken(),
          'name' => 'fieldName'.Str::generateFieldToken(),
        ]
      ])
      ->add('fieldType', ChoiceType::class, [
        'choices' => [
          '文本' => 'text',
          '网页' => 'link',
          '选项' => 'options',
          '人员' => 'user'
        ],
        'attr' => [
          'id' => Str::generateFieldToken(),
          'name' => 'fieldType'.Str::generateFieldToken(),
        ]
      ])
      ->add('fieldGroup', ChoiceType::class, [
        'choices' => $groupArr,
        'data' => $defaultValue,
        'attr' => [
          'id' => Str::generateFieldToken(),
          'name' => 'fieldGroup'.Str::generateFieldToken(),  
        ]
      ]);

    $form = $formBuilder->getForm();
    $formView = $form->createView();

    // 返回的结果数组
    $result = [];
    $result['form'] = $formView;

    if ($init) {
      $additional = $this->getSingleFieldView('entityGroup', ChoiceType::class, ['choices' => $groupArr, 'data' => $defaultValue]);
      $result['additional'] = $additional;
    }
    
    return $result;
  }

  /**
   * 获取单个字段的表单html
   * 参数 字段类型，字段选项
   * 这个方法是为了获取单个字段的html，因此字段名称并不重要，所以把它固化为'field'。
   * 字段类型（string）是必须的，选项值（array）不是必须
   * 所以先判断参数数量，字符串类型的参数是字段类型，数组是选项
   */
  public function getSingleFieldView(...$args)
  {
    $name = 'field';
    $type = null;
    $options = [];

    // 解析参数
    if (!empty($args)) {
      foreach($args as $k => $v) {
        if(is_string($v)) {
          class_exists($v) ? $type = $v : $name = $v;
        }

        if (is_array($v)) {
          $options = $v;
        }
      }
    }

    if ($type === null) {
      throw new \InvalidArgumentException('$type 参数不能为空');
    }

    $formBuilder = $this->createFormBuilder();
    $formBuilder->add($name, $type, $options);
    $form = $formBuilder->getForm();
    $formView = $form->createView();
    return $this->twig->render('ui/form/singleField.html.twig', [
      'field' => $formView
    ]);
  }

  public function addField($token, $group = null, $init = true)
  {
    $formView = $this->getFieldView($token, $group, $init);
    $form = $this->twig->render('ui/drawer/addField.html.twig', [
      'formView' => $formView['form']
    ]);
    $result = [];
    $result['form'] = $form;
    if (isset($formView['additional'])) {
      $additional = $formView['additional'];
      $result['additional'] = $additional;
    }
    return $result;
  }
}
