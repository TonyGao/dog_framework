<?php

namespace App\Form\Organization;

use App\Entity\Organization\Company;
use App\Form\BaseFormType;
use App\Service\Form\FormFieldBuilderService;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends BaseFormType
{
    private $fieldBuilder;

    public function __construct(FormFieldBuilderService $fieldBuilder)
    {
        $this->fieldBuilder = $fieldBuilder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $defaultRounded = $options['rounded'] ?? true;
        $defaultHeight = $options['height'] ?? 36;

        $this->fieldBuilder->buildFields($builder, Company::class, function ($field, &$builderOptions) use ($defaultRounded, $defaultHeight) {
            if (!isset($builderOptions['attr']['rounded'])) {
                $builderOptions['attr']['rounded'] = $defaultRounded;
            }
            if (!isset($builderOptions['attr']['height'])) {
                $builderOptions['attr']['height'] = $defaultHeight;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
            'rounded' => true,
            'height' => 36,
            'attr' => [
                'style' => 'width: 450px;', // 设置表单宽度
            ],
        ]);
    }
}
