<?php

namespace App\Form\Organization;

use App\Entity\Organization\Corporation;
use App\Entity\Platform\Entity;
use App\Form\BaseFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CorporationFormType extends BaseFormType
{
    public $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $reflectionClass = new \ReflectionClass(Corporation::class);
        $repo = $this->em->getRepository(Entity::class);
        $en = $repo->findOneBy([
            'fqn' => $reflectionClass->name,
        ]);
        $fields = $en->getProperties();
        foreach ($fields as $field) {
            $builder->add($field->getFieldName(), null, [
                'label' => $field->getComment()
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Corporation::class,
            'attr' => [
                'style' => 'width: 400px;', // 设置表单宽度
            ],
        ]);
    }
}
