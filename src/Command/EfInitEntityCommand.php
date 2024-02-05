<?php

namespace App\Command;

use App\Annotation\Ef;
use App\Lib\Str;
use App\Entity\Platform\Entity;
use App\Entity\Platform\EntityProperty;
use App\Entity\Platform\EntityPropertyGroup;
use App\Repository\Platform\EntityRepository;
use App\Repository\Platform\EntityPropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Nette\PhpGenerator\PhpFile;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use ReflectionClass;

#[AsCommand(
    name: 'ef:entity',
    description: '将模型文件初始化到数据库',
)]
class EfInitEntityCommand extends Command
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('--init', null, InputOption::VALUE_NONE, '初始化模型文件到数据库')
            ->addOption('--listPerpertyGroup', null, InputOption::VALUE_NONE, '列出所有的属性分组');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        /**
         * 遍历src/Entity目录的模型文件，并初始化到数据库
         */
        if ($input->getOption('init')) {
            $finder = new Finder();
            $finder->files()->in('src/Entity');

            if ($finder->hasResults()) {
                $repo = $this->em->getRepository(EntityPropertyGroup::class);
                $root = $repo->findOneby(['type' => 'root']);

                if ($root === null) {
                    $rootET = new EntityPropertyGroup;
                    $rootET->setName('root')
                        ->setLabel('root')
                        ->setType('root');
                    $this->em->persist($rootET);
                    $this->em->flush();

                    $root = $repo->findOneby(['type' => 'root']);
                }

                $finder->files();

                foreach ($finder as $file) {
                    $absolutFilePath = $file->getRealPath();

                    $filePath = $absolutFilePath;
                    $file = PhpFile::fromCode(file_get_contents($filePath));
                    $key = array_key_first($file->getClasses());
                    $class = $file->getClasses()[$key];

                    $isBusinessEntity = Str::isBusinessEntity($class->getComment());

                    if ($class->isClass() && $isBusinessEntity) {
                        $entity = new Entity();
                        $entityToken = sha1(random_bytes(10));
                        $entityClass = $class->getNamespace()->getName() . '\\' . $class->getName();
                        $metaData = $this->em->getClassMetadata($entityClass);
                        $tableName = $metaData->getTableName();
                        $entity->setName($class->getName() . '.php')
                            ->setToken($entityToken)
                            ->setFqn($metaData->name)
                            ->setIsCustomized(false)
                            ->setClassName($class->getName())
                            ->setDataTableName($tableName);

                        $dynamicClass = new $entityClass();
                        $reflectionClass = new ReflectionClass($dynamicClass::class);

                        // $properties = $metaData->getReflectionProperties();
                        $fields = $metaData->fieldMappings;

                        // 补充原fieldMappings不包含的Entity类型的字段属性
                        $associationMappings = $metaData->associationMappings;

                        foreach($associationMappings as $key=>$mappings) {
                            foreach($mappings as $mapping) {
                                $fields[$key]['fieldName'] = $mappings['fieldName'];
                                $fields[$key]['type'] = 'entity';
                                $fields[$key]['targetEntity'] = $mappings['targetEntity'];
                                switch ($mappings['type']) {
                                    case 1:
                                        $associationType = 'OneToOne';
                                        break;
                                    case 2:
                                        $associationType = 'ManyToOne';
                                        break;
                                    case 3:
                                        $associationType = "ManyToMany";
                                        break;
                                    case 4:
                                        $associationType = "OneToMany";
                                        break;
                                    default:
                                        $associationType = null;
                                        break;
                                }
                                $fields[$key]['associationType'] = $associationType;
                                $fields[$key]['scale'] = null;
                                $fields[$key]['length'] = null;
                                $fields[$key]['unique'] = null;
                                $fields[$key]['nullable'] = null;
                                $fields[$key]['precision'] = null;
                                if (array_key_exists('sourceToTargetKeyColumns', $mappings)) {
                                    $fields[$key]['fieldName'] = key($mappings['sourceToTargetKeyColumns']);
                                    $fields[$key]['targetId'] = $mappings['sourceToTargetKeyColumns'][key($mappings['sourceToTargetKeyColumns'])];
                                }
                            }
                        }

                        unset($fields['deletedAt']);
                        unset($fields['createdAt']);
                        unset($fields['updatedAt']);
                        unset($fields['createdBy']);
                        unset($fields['updatedBy']);

                        // 初始化entity类型的属性分组
                        $entityGroup = new EntityPropertyGroup();
                        $entityGroup->setName($class->getName())
                            ->setLabel($class->getName())
                            ->setType('entity')
                            ->setToken($entityToken)
                            ->setFqn($metaData->name)
                            ->setParent($root);

                        $this->em->persist($entityGroup);
                        $this->em->flush();

                        /**
                         * 将Entity中的字段汇总到$fields数组中
                         * 默认包含的字段，包括：
                         * fieldName Entity字段名称
                         * type 包括 string, integer
                         * scale
                         * length 字符串长度
                         * unique 唯一性
                         * nullable 可为空
                         * precision 精确度
                         * columnName mysql 数据库中的字段名称
                         * ---------------------------------
                         * 关联查询的字段默认不在此变量，前边已经
                         * 处理加入此变量，其字段包括：
                         * fieldName Entity字段名称
                         * type 值固定为 "EntityType"
                         * targetEntity 如 "App\Entity\Organization\Company"
                         * associationType 原type重命名为此变量名
                         *     1 --> OneToOne
                         *     2 --> ManyToOne
                         *     3 --> ManyToMany(maybe)
                         *     4 --> OneToMany
                         */
                        dump($fields);
                        foreach ($fields as $key => $field) {
                            $fieldName = $key;
                            $annotationField = $reflectionClass->getProperty($fieldName);
                            $reader = new AnnotationReader();
                            $anno = $reader->getPropertyAnnotation(
                                $annotationField,
                                Ef::class
                            );

                            $group = null;
                            $isBusinessField = false;
                            if ($anno !== null) {
                                $group = $anno->getValue()['group'];
                                $isBusinessField = $anno->getValue()['bf'];
                            }

                            if ($isBusinessField) {
                                $property = new EntityProperty();
                                $propertyToken = sha1(random_bytes(10));

                                $comment = Str::getComment($class->properties[$fieldName]->getComment());
                                // if (!array_key_exists('columnName', $field)) {
                                //     dump($field);
                                // }
                                $property->setToken($propertyToken)
                                    ->setIsCustomized(false)
                                    ->setPropertyName($fieldName)
                                    ->setComment($comment)
                                    ->setType($field['type'])
                                    ->setFieldName($field['fieldName'])
                                    ->setUniqueable($field['unique'])
                                    ->setNullable($field['nullable'])
                                    ->setBusinessField(true)
                                    ;

                                // 如果是entity类型就额外设置目标id和目标entity，以及formType
                                $targetIdExists = ['ManyToOne', 'OneToOne'];
                                if ($field['type'] == 'entity') {
                                    if (in_array($field['associationType'], $targetIdExists)) {
                                        $property->setTargetId($field['targetId']);
                                    }

                                    $property->setTargetEntity($field['targetEntity']);

                                    if ($field['targetEntity'] === 'App\Entity\Organization\Department') {
                                        $property->setType('department');
                                    }

                                    // 根据targetEntity获得formType
                                    $property->setFormType(Str::convertFormTypeFromTargetEntity($field['targetEntity']));
                                }

                                if ($field['precision'] !== null) {
                                    $property->setDecimalPrecision($field['precision']);
                                }

                                if ($field['scale'] !== null) {
                                    $property->setDecimalScale($field['scale']);
                                }

                                if ($field['length'] !== null) {
                                    $property->setLength($field['length']);
                                }

                                // Entity 下的 Property
                                if ($group === null) {
                                    $entityProperty = new EntityPropertyGroup();
                                    $entityProperty->setName($fieldName)
                                        ->setLabel($fieldName)
                                        ->setType("property")
                                        ->setToken($propertyToken)
                                        ->setParent($entityGroup);
                                    $this->em->persist($entityProperty);
                                }

                                // Entity 下的 Group
                                if ($group !== null) {
                                    $propertyGroup = $repo->findOneBy(['name' => $group]);
                                    if ($propertyGroup === null) {
                                        $propertyGroup = new EntityPropertyGroup();
                                        $propertyGroup->setName($group)
                                           ->setLabel($group)
                                           ->setType('group')
                                           ->setParent($entityGroup);
                                        $this->em->persist($propertyGroup);
                                        $this->em->flush();

                                        $propertyGroup = $repo->findOneBy(['name' => $group]);
                                    }

                                    // Group 下的 Property
                                    $groupProperty = new EntityPropertyGroup();
                                    $groupProperty->setName($fieldName)
                                        ->setLabel($fieldName)
                                        ->setType('property')
                                        ->setToken($propertyToken)
                                        ->setParent($propertyGroup);

                                    $this->em->persist($groupProperty);
                                    $this->em->flush();
                                }

                                $entity->addProperty($property);
                                $this->em->persist($property);
                                $this->em->flush();
                            }
                        }

                        // $dynamicClass = new $entityClass();
                        // $reflectionClass = new ReflectionClass($dynamicClass::class);
                        // $annotationReader = new AnnotationReader();
                        // $classAnnotations = $annotationReader->getClassAnnotation($reflectionClass, 'Doctrine\ORM\Mapping\Table');
                        // dump($classAnnotations->name);

                        // $repoClass = 'App\Repository\Organization\CorporationRepository';
                        // $repoInstance = new $repoClass();

                        $this->em->persist($entity);
                        $this->em->flush();
                    }
                }
            }
        }

        if ($input->getOption('listPerpertyGroup')) {
            $repo = $this->em->getRepository(EntityPropertyGroup::class);
            $tree = $repo->childrenHierarchy();
        }

        $io->success('已成功初始化所有Entity文件到数据库');

        return Command::SUCCESS;
    }
}
