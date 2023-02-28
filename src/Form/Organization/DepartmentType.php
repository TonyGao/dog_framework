<?php

namespace App\Form\Organization;

use App\Lib\Str;
use App\Lib\Arr;
use App\Entity\Organization\Department;
use App\Entity\Organization\Company;
use App\Entity\Platform\Entity;
use App\Entity\Platform\EntityProperty;
use App\Repository\Organization\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Translation\TranslatableMessage;

class DepartmentType extends AbstractType
{
  public $em;
  private $companyRepo;
  public function __construct(EntityManagerInterface $em, CompanyRepository $comRepo)
  {
    $this->em = $em;
    $this->companyRepo = $comRepo;
  }

  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    // $reflectionClass = new \ReflectionClass(Department::class);
    // $repo = $this->em->getRepository(Entity::class);
    // $en = $repo->findOneBy([
    //     'fqn' => $reflectionClass->name,
    // ]);

    // $fields = $this->em->getRepository(EntityProperty::class)
    //   ->findBy(['entity' => $en],['id' => 'ASC']);
    // foreach ($fields as $field) {
    //     $arr = [];
    //     if ($field->getValidation()) {
    //         $validation = $field->getValidation();
    //         $arr = Arr::transValtoAttr($validation);
    //     }
    //     $classType = Str::convertFormType($field->getType());
    //     $builder->add($field->getFieldName(), $classType, [
    //         'label' => $field->getComment(),
    //         'attr' => $arr,
    //         'required' => $arr['required'] ?? false,
    //     ]);
    // }
    $builder
        ->add('name')
        ->add('alias')
        ->add('company', EntityType::class, [
          'class' => Company::class,
          'choices' => $this->companyRepo->allCompany(),
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
