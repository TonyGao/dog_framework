<?php

namespace App\Command;

use App\Lib\Str;
use App\Entity\Platform\Entity;
use App\Entity\Platform\EntityProperty;
use App\Repository\Platform\EntityRepository;
use App\Repository\Platform\EntityPropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Annotations\AnnotationReader;
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

use function PHPSTORM_META\map;

#[AsCommand(
    name: 'ef:initEntity',
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
            ->addOption('--init', null, InputOption::VALUE_NONE, '初始化模型文件到数据库');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        /**
         * 便利src/Entity目录的模型文件，并初始化到数据库
         */
        if ($input->getOption('init')) {
            $finder = new Finder();
            $finder->files()->in('src/Entity');

            if ($finder->hasResults()) {
                $finder->files();

                foreach ($finder as $file) {
                    $absolutFilePath = $file->getRealPath();
                    $fileName = $file->getRelativePathname();

                    $filePath = $absolutFilePath;
                    $file = PhpFile::fromCode(file_get_contents($filePath));
                    $key = array_key_first($file->getClasses());
                    $class = $file->getClasses()[$key];

                    if ($class->isClass()) {
                        $entity = new Entity();
                        $entityClass = $class->getNamespace()->getName() . '\\' . $class->getName();
                        $metaData = $this->em->getClassMetadata($entityClass);
                        $tableName = $metaData->getTableName();

                        $entity->setName($class->getName().'.php')
                        ->setToken(sha1(random_bytes(10)))
                        ->setIsCustomized(false)
                        ->setClassName($class->getName())
                        ->setDataTableName($tableName);

                        // $properties = $metaData->getReflectionProperties();
                        $fields = $metaData->fieldMappings;
                        unset($fields['deletedAt']);
                        unset($fields['createdAt']);
                        unset($fields['updatedAt']);
                        unset($fields['createdBy']);
                        unset($fields['updatedBy']);
                        foreach($fields as $field) {
                            $property = new EntityProperty();
                            $fieldName = $field['fieldName'];
                            $comment = Str::getComment($class->properties[$fieldName]->getComment());
                            $property->setToken(sha1(random_bytes(10)))
                                ->setIsCustomized(false)
                                ->setPropertyName($fieldName)
                                ->setComment($comment)
                                ->setType($field['type'])
                                ->setFieldName($field['columnName'])
                                ->setUniqueable($field['unique'])
                                ->setNullable($field['nullable']);

                            if ($field['precision'] !== null) {
                                $property->setDecimalPrecision($field['precision']);
                            }

                            if ($field['scale'] !== null) {
                                $property->setDecimalScale($field['scale']);
                            }

                            if ($field['length'] !== null) {
                                $property->setLength($field['length']);
                            }

                            $entity->addProperty($property);
                            $this->em->persist($property);
                            $this->em->flush();
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

        $io->success('已成功初始化所有Entity文件到数据库');

        return Command::SUCCESS;
    }
}
