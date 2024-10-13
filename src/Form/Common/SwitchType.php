<?php

namespace App\Form\Common;

use App\Form\BaseFormType;
use Symfony\Component\Form\Extension\Core\DataTransformer\BooleanToStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SwitchType extends BaseFormType
{
  /**
   * {@inheritdoc}
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->setData($options['data'] ?? false);
    $builder->addViewTransformer(new BooleanToStringTransformer($options['value'], $options['false_values']));
  }

  /**
   * {@inheritdoc}
   */
  public function buildView(FormView $view, FormInterface $form, array $options)
  {
    $view->vars = array_replace($view->vars, [
      'value' => $options['value'],
      'checked' => null !== $form->getViewData(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    $emptyData = function (FormInterface $form, $viewData) {
      return $viewData;
    };

    $resolver->setDefaults([
      'value' => '1',
      'empty_data' => $emptyData,
      'compound' => false,
      'false_values' => [null, '0'],
      'invalid_message' => 'The switch has an invalid value.',
      'is_empty_callback' => static function($modelData): bool {
        return false === $modelData;
      },
    ]);

    $resolver->setAllowedTypes('false_values', 'array');
  }

  /**
   * {@inheritdoc}
   */
  public function getBlockPrefix(): string
  {
    return 'switch';
  }
}
