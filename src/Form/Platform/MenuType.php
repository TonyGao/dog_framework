<?php

namespace App\Form\Platform;

use App\Entity\Platform\Menu;
use Symfony\Component\Form\AbstractType;
use App\Repository\Platform\MenuRepository;
use App\Service\Form\FormFieldBuilderService;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
  private $formFieldBuilder;
  private $menuRepo;

  public function __construct(FormFieldBuilderService $formFieldBuilder, MenuRepository $menuRepo)
  {
    $this->formFieldBuilder = $formFieldBuilder;
    $this->menuRepo = $menuRepo;
  }

  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $this->formFieldBuilder->buildFields($builder, Menu::class);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
      $resolver->setDefaults([
          'data_class' => Menu::class,
      ]);
  }
}