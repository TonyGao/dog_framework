<?php

namespace App\Form;

use App\Entity\Organization\Corporation;
use App\Entity\Platform\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CorporationType extends AbstractType
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
            $builder->add($field->getFieldName());
        }
        $builder->add('save', SubmitType::class, ['label' => 'Create Task']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Corporation::class,
        ]);
    }
}
