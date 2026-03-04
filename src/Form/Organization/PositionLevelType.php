<?php

namespace App\Form\Organization;

use App\Entity\Organization\PositionLevel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionLevelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => '级别名称',
                'required' => true,
            ])
            ->add('code', TextType::class, [
                'label' => '级别编码',
                'required' => false,
            ])
            ->add('levelOrder', IntegerType::class, [
                'label' => '级别序号',
                'required' => true,
                'help' => '数字越小级别越高',
            ])
            ->add('salaryMin', NumberType::class, [
                'label' => '薪资范围下限',
                'required' => false,
                'scale' => 2,
            ])
            ->add('salaryMax', NumberType::class, [
                'label' => '薪资范围上限',
                'required' => false,
                'scale' => 2,
            ])
            ->add('description', TextareaType::class, [
                'label' => '级别描述',
                'required' => false,
            ])
            ->add('state', CheckboxType::class, [
                'label' => '启用',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PositionLevel::class,
        ]);
    }
}
