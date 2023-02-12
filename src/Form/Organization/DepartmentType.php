<?php

namespace App\Form\Organization;

use App\Entity\Organization\Department;
use App\Entity\Organization\Company;
use App\Entity\Platform\Entity;
use App\Entity\Platform\EntityProperty;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class DepartmentType extends AbstractType
{
  public $em;
  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
  }

  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
        ->add('name')
        ->add('alias')
        ->add('company', EntityType::class, [
          'class' => Company::class,
          'choice_label' => 'name'
        ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
      $resolver->setDefaults([
          'data_class' => Department::class,
      ]);
  }
}
