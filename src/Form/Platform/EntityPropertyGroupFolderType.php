<?php

namespace App\Form\Platform;

use Symfony\Component\Form\AbstractType;
use App\Entity\Platform\EntityPropertyGroup;
use App\Form\BaseFormType;
use App\Service\Form\FormFieldBuilderService;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EntityPropertyGroupFolderType extends BaseFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, ['label' => '文件夹名'])
          ->add('parent', EntityType::class, [
            'label' => '上级目录',
            'class' => EntityPropertyGroup::class,
            'choice_label' => 'label',
            'placeholder' => '请选择上级目录',
            'required' => true,
            ])
          ->add('type', TextType::class, ['attr'=>[
            'readonly' => true,
          ],
          'data' => 'namespace',
          'label' => '类型'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EntityPropertyGroup::class, // 设置数据类为 EntityPropertyGroup
            'readonly' => false,
            'attr' => [
                'style' => 'width: 450px;', // 设置表单宽度
            ],
        ]);
    }
}
