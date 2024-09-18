<?php

namespace App\Form\Organization;

use App\Lib\Str;
use App\Lib\Arr;
use App\Service\Form\FormFieldBuilderService;
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

class OrgDepartmentType extends AbstractType
{
  private $formFieldBuilder;
  private $companyRepo;

  public function __construct(FormFieldBuilderService $formFieldBuilder, CompanyRepository $comRepo)
  {
    $this->formFieldBuilder = $formFieldBuilder;
    $this->companyRepo = $comRepo;
  }

  // public function buildForm(FormBuilderInterface $builder, array $options): void
  // {
  //   $reflectionClass = new \ReflectionClass(Department::class);
  //   $repo = $this->em->getRepository(Entity::class);
  //   $en = $repo->findOneBy([
  //       'fqn' => $reflectionClass->name,
  //   ]);

  //   $fields = $this->em->getRepository(EntityProperty::class)
  //     ->findBy(['entity' => $en],['id' => 'ASC']);
  //   foreach ($fields as $field) {
  //       $arr = [];
  //       if ($field->getValidation()) {
  //           $validation = $field->getValidation();
  //           $arr = Arr::transValtoAttr($validation);
  //       }
  //       $classType = Str::convertFormType($field->getType());
  //       $options = [
  //         'label' => $field->getComment(),
  //         'attr' => $arr,
  //         'required' => $arr['required'] ?? false,
  //       ];

  //       if ($field->getTargetEntity() != null) {
  //         $options['class'] = $field->getTargetEntity();
  //       }

  //       if ($field->getTargetEntity() == Company::class) {
  //         $options['choices'] = $this->companyRepo->allCompany();
  //         $options['choice_label'] = 'alias';
  //       }
  //       $builder->add($field->getPropertyName(), $classType, $options);
  //   }
  // }
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
      $this->formFieldBuilder->buildFields($builder, Department::class, function ($field, &$options) {
          if ($field->getTargetEntity() === Company::class) {
              $options['choices'] = $this->companyRepo->allCompany();
              $options['choice_label'] = 'alias';
          }
      });
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
      $resolver->setDefaults([
          'data_class' => Department::class,
      ]);
  }
}
