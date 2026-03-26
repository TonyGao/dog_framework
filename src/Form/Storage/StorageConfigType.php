<?php

namespace App\Form\Storage;

use App\Entity\Storage\StorageConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StorageConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => '存储名称', 'required' => true])
            ->add('adapterType', ChoiceType::class, [
                'label' => '驱动类型',
                'required' => true,
                'placeholder' => '请选择驱动类型',
                'choices' => [
                    '本地存储' => 'local',
                    'S3 兼容存储' => 's3',
                ],
            ])
            ->add('isDefault', CheckboxType::class, [
                'label' => '设为默认',
                'required' => false,
            ])
            ->add('cdnDomain', TextType::class, [
                'label' => 'CDN 域名',
                'required' => false,
            ])
            ->add('directory_strategy', ChoiceType::class, [
                'mapped' => false,
                'label' => '文件存储策略',
                'required' => true,
                'choices' => [
                    '按年月日 (YYYY/MM/DD)' => 'Y/m/d',
                    '按年月 (YYYY/MM)' => 'Y/m',
                    '按年份 (YYYY)' => 'Y',
                    '不创建子目录' => 'none',
                ],
                'data' => 'Y/m', // Default to Year/Month as requested
                'help' => '上传文件时自动创建的日期子目录格式'
            ]);

        // Add unmapped fields for config JSON
        $builder->add('endpoint', TextType::class, ['mapped' => false, 'required' => false, 'label' => 'Endpoint']);
        $builder->add('region', TextType::class, ['mapped' => false, 'required' => false, 'label' => 'Region']);
        $builder->add('bucket', TextType::class, ['mapped' => false, 'required' => false, 'label' => 'Bucket']);
        $builder->add('access_key', TextType::class, ['mapped' => false, 'required' => false, 'label' => 'Access Key']);
        $builder->add('secret_key', TextType::class, ['mapped' => false, 'required' => false, 'label' => 'Secret Key']);
        $builder->add('directory', TextType::class, [
            'mapped' => false,
            'required' => false,
            'label' => '存储目录（相对于 public）',
            'attr' => ['placeholder' => '默认为 uploads'],
            'help' => '文件将存储在 public/{目录} 下'
        ]);

        // Populate form fields from JSON config
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $config = $event->getData();
            $form = $event->getForm();

            if ($config && $config->getConfig()) {
                $data = $config->getConfig();
                foreach ($data as $key => $value) {
                    if ($form->has($key)) {
                        $form->get($key)->setData($value);
                    }
                }
            }
        });

        // Save form fields to JSON config
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $config = $event->getData();
            $form = $event->getForm();

            $configData = [];
            $keys = ['endpoint', 'region', 'bucket', 'access_key', 'secret_key', 'directory', 'directory_strategy'];
            
            foreach ($keys as $key) {
                if ($form->has($key) && $form->get($key)->getData()) {
                    $configData[$key] = $form->get($key)->getData();
                }
            }

            // Set default directory if not provided for local
            if ($config->getAdapterType() === 'local' && empty($configData['directory'])) {
                $configData['directory'] = 'uploads';
            }

            $config->setConfig($configData);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StorageConfig::class,
        ]);
    }
}
