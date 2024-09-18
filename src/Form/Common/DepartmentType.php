<?php

namespace App\Form\Common;

use App\Service\Form\FormFieldBuilderService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Organization\Department;
use Symfony\Component\Form\Extension\Core\Type\EntityType as SymfonyEntityType;

class DepartmentType extends AbstractType
{
  /**
   * {@inheritdoc}
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->setData($options['data'] ?? false);
  }


  /**
   * {@inheritdoc}
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults([
      'class' => Department::class
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getBlockPrefix(): string
  {
    return 'department';
  }

  // public function getParent()
  // {
  //   return SymfonyEntityType::class;
  // }
}
