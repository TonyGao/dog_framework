<?php

namespace App\Controller\Test;

use App\Entity\Organization\Department;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

enum TestStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}

class ChoiceFieldsController extends AbstractController
{
    #[Route('/test/choice_fields', name: 'test_choice_fields')]
    public function index(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('choice', ChoiceType::class, [
                'choices' => [
                    'Option 1' => '1',
                    'Option 2' => '2',
                    'Option 3' => '3',
                ],
                'label' => 'ChoiceType',
                'required' => false,
            ])
            ->add('enum', EnumType::class, [
                'class' => TestStatus::class,
                'label' => 'EnumType',
                'required' => false,
            ])
            ->add('entity', EntityType::class, [
                'class' => Department::class,
                'choice_label' => 'name',
                'label' => 'EntityType',
                'required' => false,
            ])
            ->add('country', CountryType::class, [
                'label' => 'CountryType',
                'required' => false,
            ])
            ->add('language', LanguageType::class, [
                'label' => 'LanguageType',
                'required' => false,
            ])
            ->add('locale', LocaleType::class, [
                'label' => 'LocaleType',
                'required' => false,
            ])
            ->add('timezone', TimezoneType::class, [
                'label' => 'TimezoneType',
                'required' => false,
            ])
            ->add('currency', CurrencyType::class, [
                'label' => 'CurrencyType',
                'required' => false,
            ])
            ->getForm();

        return $this->render('test/choice_fields.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
