<?php

namespace App\Service\Form;

use App\Lib\Arr;
use App\Lib\Str;
use App\Service\BaseService;
use App\Entity\Platform\Entity;
use App\Entity\Platform\EntityProperty;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormBuilderInterface;

class FormFieldBuilderService extends BaseService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildFields(FormBuilderInterface $builder, string $entityClass, callable $customOptionsCallback = null): void
    {
        $reflectionClass = new \ReflectionClass($entityClass);
        $repo = $this->em->getRepository(Entity::class);
        $en = $repo->findOneBy([
            'fqn' => $reflectionClass->name,
        ]);

        $fields = $this->em->getRepository(EntityProperty::class)
            ->findBy(['entity' => $en], ['orderNum' => 'ASC']);

        foreach ($fields as $field) {
            $arr = [];
            if ($field->getValidation()) {
                $validation = $field->getValidation();
                $arr = Arr::transValtoAttr($validation);
            }

            $classType = Str::convertFormType($field->getType());
            $options = [
                'label' => $field->getComment(),
                'attr' => $arr,
                'required' => $arr['required'] ?? false,
            ];

            if ($field->getTargetEntity() !== null) {
                $options['class'] = $field->getTargetEntity();
            }

            // Use custom options callback if provided
            if ($customOptionsCallback) {
                $customOptionsCallback($field, $options);
            }

            $property = $field->getPropertyName();
            $builder->add($property, $classType, $options);
        }
    }

    public function getEntityManager() {
        return $this->em;
    }
}
