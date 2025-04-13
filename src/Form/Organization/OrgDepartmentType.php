<?php

namespace App\Form\Organization;

use App\Service\Form\FormFieldBuilderService;
use App\Entity\Organization\Department;
use App\Entity\Organization\Company;
use App\Form\BaseFormType;
use App\Repository\Organization\CompanyRepository;
use App\Repository\Organization\UserRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\Common\DepartmentTypeTransfer;

class OrgDepartmentType extends BaseFormType
{
  public function __construct(
    private FormFieldBuilderService $formFieldBuilder,
    private CompanyRepository $companyRepo,
    private UserRepository $userRepo,
    private DepartmentTypeTransfer $departmentTypeTransfer
  ) {
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
      $this->formFieldBuilder->buildFields($builder, Department::class, function ($field, &$fieldOptions) {
          if ($field->getTargetEntity() === Company::class) {
              $fieldOptions['choices'] = $this->companyRepo->allCompany();
              $fieldOptions['choice_label'] = 'alias';
          }

          if ($field->getPropertyName() === 'manager') {
              $fieldOptions['multiple'] = true;
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
