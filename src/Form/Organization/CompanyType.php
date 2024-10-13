<?php

namespace App\Form\Organization;

use App\Lib\Str;
use App\Lib\Arr;
use App\Entity\Organization\Company;
use App\Entity\Platform\Entity;
use App\Entity\Platform\EntityProperty;
use App\Form\BaseFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CompanyType extends BaseFormType
{
    public $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $reflectionClass = new \ReflectionClass(Company::class);
        $repo = $this->em->getRepository(Entity::class);
        $en = $repo->findOneBy([
            'fqn' => $reflectionClass->name,
        ]);
        $fields = $this->em->getRepository(EntityProperty::class)
          ->findBy(['entity' => $en],['id' => 'ASC']);
        foreach ($fields as $field) {
            $arr = [];
            if ($field->getValidation()) {
                $validation = $field->getValidation();
                $arr = Arr::transValtoAttr($validation);
            }
            $type = $field->getType();
            $classType = Str::convertFormType($type);

            $builderOptions = [
                'label' => $field->getComment(),
                'attr' => $arr,
                'required' => $arr['required'] ?? false,
            ];

            if ($type === 'entity') {
                $builderOptions['class'] = $field->getTargetEntity();
            }

            $builder->add($field->getPropertyName(), $classType, $builderOptions);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
            'attr' => [
                'style' => 'width: 450px;', // 设置表单宽度
            ],
        ]);
    }
}
