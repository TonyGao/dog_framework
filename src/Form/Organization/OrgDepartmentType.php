<?php

namespace App\Form\Organization;

use App\Service\Form\FormFieldBuilderService;
use App\Entity\Organization\Department;
use App\Entity\Organization\Company;
use App\Form\BaseFormType;
use App\Repository\Organization\CompanyRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\Common\DepartmentTypeTransfer;

class OrgDepartmentType extends BaseFormType
{
  private $formFieldBuilder;
  private $companyRepo;

  public function __construct(
    FormFieldBuilderService $formFieldBuilder,
    CompanyRepository $comRepo,
    private DepartmentTypeTransfer $departmentTypeTransfer
  ) {
    $this->formFieldBuilder = $formFieldBuilder;
    $this->companyRepo = $comRepo;
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
      $this->formFieldBuilder->buildFields($builder, Department::class, function ($field, &$fieldOptions) {
          if ($field->getTargetEntity() === Company::class) {
              $fieldOptions['choices'] = $this->companyRepo->allCompany();
              $fieldOptions['choice_label'] = 'alias';
          }
      });
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
      $em = $this->formFieldBuilder->getEntityManager();
      $resolver->setDefaults([
          'em' => $em,
          'class' => Department::class,
          'attr' => [
            'style' => 'width: 450px;', // 设置表单宽度
          ],
      ]);
  }
}
