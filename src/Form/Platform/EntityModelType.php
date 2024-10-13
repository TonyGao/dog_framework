<?php

namespace App\Form\Platform;

use App\Entity\Platform\Entity;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\Platform\EntityPropertyGroup;
use App\Form\BaseFormType;
use App\Service\Form\FormFieldBuilderService;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EntityModelType extends BaseFormType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    /**
     * $syncData 是用来为dom标记回写其他字段的配置(前端行为)
     * 例如下边的内容 entity_model_name, entity_model_fqn, entity_model_dataTableName 分别为三个 dom id,
     * callback 的内容是各自的回调函数
     * addExtension 是为以当前字段的值为基础增加php扩展名并赋值给目标dom 如 HelloWorld.php
     * addToFqn 是以当前字段的值为基础，结合目标的value属性的值，组成fqn 如 App\Entity\Organization\HelloWorld
     * tableize 是以当前字段的值为基础，转成下划线连接的形式，注意：只有大写字母开头的单词才会转下划线
     * 
     * 前端部分的实现可参照 inputPlus.js
     */
    $syncData = [
      "entity_model_name" => [
        "callback" => ["addExtension" => "php"]
      ],
      "entity_model_fqn" => [
        "callback" => ["addToFqn" => true]
      ],
      "entity_model_dataTableName" => [
        "callback" => ["tableize" => true]
      ]
    ];

    $builder->add('className', TextType::class, [
        'label' => '类名',
        'attr' => [
          'format' => "data-pascal-style",
          'data-sync' => json_encode($syncData),
        ]
      ])
      ->add('name', TextType::class, [
        'label' => '文件名',
        'attr' => [
          'readonly' => true,
        ]
      ])
      ->add('code', null, ['label' => '编号'])
      ->add('token', TextareaType::class, ['attr' => [
        'readonly' => true,
      ]])
      ->add('fqn', TextType::class, ['attr' => [
        'readonly' => true,
      ]])
      ->add('dataTableName', TextType::class, ['attr' => [
        'readonly' => true,
      ], 'label' => '数据库表名'])
      ->add('type', HiddenType::class, [
        'data' => $options['type'], // 默认值从选项中获取
        'mapped' => false,
      ]);

    // 添加虚拟字段：parent（上级目录）
    $builder->add('parent', EntityType::class, [
      'label' => '上级目录',
      'class' => EntityPropertyGroup::class,
      'choice_label' => 'label',
      'placeholder' => '请选择上级目录',
      'mapped' => false,
      'required' => false,
      'data' => $options['parent_default'],
    ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Entity::class, // 设置数据类为 EntityPropertyGroup
      'readonly' => false,
      'parent_default' => null,
      'attr' => [
        'style' => 'width: 450px;', // 设置表单宽度
      ],
      'type' => null,
    ]);
  }
}
