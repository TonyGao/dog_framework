<?php

namespace App\Form\Common;

use App\Entity\Organization\Department;
use App\Form\BaseFormType;
use App\Service\Form\FormFieldBuilderService;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\Common\DepartmentTypeTransfer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EntityType as SymfonyEntityType;

class DepartmentType extends BaseFormType
{

  public function __construct(
    private DepartmentTypeTransfer $departmentTypeTransfer
  ) {}

  /**
   * {@inheritdoc}
   */
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder->setData($options['data'] ?? false)
      ->addModelTransformer($this->departmentTypeTransfer);
  }


  /**
   * {@inheritdoc}
   */
  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'class' => Department::class,
      'compound' => false,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getBlockPrefix(): string
  {
    return 'department';
  }
}
