<?php

namespace App\Form\Organization;

use App\Entity\Organization\Department;
use App\Entity\Organization\Position;
use App\Entity\Organization\PositionLevel;
use App\Entity\Platform\OptionValue;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => '岗位名称',
                'required' => true,
            ])
            ->add('code', TextType::class, [
                'label' => '岗位编码',
                'required' => false,
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => 'name',
                'label' => '所属部门',
                'required' => false,
                'placeholder' => '-- 请选择所属部门 --',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->where('d.type = :type')
                        ->setParameter('type', 'department')
                        ->orderBy('d.name', 'ASC');
                },
            ])
            ->add('type', EntityType::class, [
                'class' => OptionValue::class,
                'choice_label' => 'stringValue',
                'label' => '岗位类型',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('o')
                        ->where('o.code = :key')
                        ->setParameter('key', 'position_type')
                        ->orderBy('o.orderNum', 'ASC');
                },
            ])
            ->add('level', EntityType::class, [
                'class' => PositionLevel::class,
                'choice_label' => 'name',
                'label' => '岗位级别',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('pl')
                        ->where('pl.state = :state')
                        ->setParameter('state', true)
                        ->orderBy('pl.levelOrder', 'ASC');
                },
            ])
            ->add('parent', EntityType::class, [
                'class' => Position::class,
                'choice_label' => 'name',
                'label' => '上级岗位',
                'required' => false,
                'placeholder' => '-- 请选择上级岗位 --',
            ])
            ->add('responsibility', TextareaType::class, [
                'label' => '岗位职责',
                'required' => false,
            ])
            ->add('requirement', TextareaType::class, [
                'label' => '任职要求',
                'required' => false,
            ])
            ->add('headcount', IntegerType::class, [
                'label' => '编制人数',
                'required' => false,
            ])
            ->add('state', CheckboxType::class, [
                'label' => '启用',
                'required' => false,
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => '排序号',
                'required' => false,
            ])
            ->add('remark', TextareaType::class, [
                'label' => '备注',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Position::class,
        ]);
    }
}