<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        // 这里可以设置默认选项，但最简单的实现可以留空
        $resolver->setDefaults([]);
    }
}
