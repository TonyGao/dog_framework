<?php

namespace App\Command;

use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\ClassType;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

#[AsCommand(
    name: 'ef:model',
    description: 'Add a dynamic attribute to an entity',
)]
class EfAddDynamicAttributeCommand extends Command
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();
        $this->kernel = $kernel;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('argument', InputArgument::OPTIONAL, '模型命令')
            ->addOption('test', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 获取应用程序的根目录
        $rootDir = $this->kernel->getProjectDir();
        $entityDir = $rootDir . '/src/Entity';
        $io = new SymfonyStyle($input, $output);
        $argument = $input->getArgument('argument');

        $finder = new Finder();
        $finder->files()->in($entityDir);
        $namespaceQuestion = [];

        if ($finder->hasResults()) {
            $finder->files();
            $namespaceTable = [];
            foreach ($finder as $file) {
                $absolutFilePath = $file->getRealPath();
                $file = PhpFile::fromCode(file_get_contents($absolutFilePath));

                $key = array_key_first($file->getClasses());
                $class = $file->getClasses()[$key];
                $entityClass = $class->getNamespace()->getName() . '\\' . $class->getName();
                $namespaceQuestion[] = $entityClass;
                $namespaceTable[][] = $entityClass;
            }
        }



        if ($argument === 'list') {

            $table = new Table($io);
            $table
                ->setHeaders(['Model Namespace'])
                ->setRows($namespaceTable)
            ;
            $table->render();
        }

        if ($argument === 'add') {
            $helper = $this->getHelper('question');

            // $callback = function (string $userInput) use ($namespaceQuestion): array {
            //     return array_filter($namespaceQuestion, function ($option) use ($userInput) {
            //         return stripos($option, $userInput) !== false;
            //     });
            // };

            // $question = new Question('请选择你需要添加属性的模型');
            // $question->setAutocompleterCallback($callback);
            // $chosedModel = $helper->ask($input, $output, $question);
            // $output->writeln('You have just selected: '.$chosedModel);

            $question = new ChoiceQuestion(
                '请选择你需要添加属性的模型',
                // choices can also be PHP objects that implement __toString() method
                $namespaceQuestion,
                0
            );
            $question->setErrorMessage('模型 %s 并不存在');
        
            $model = $helper->ask($input, $output, $question);
            $output->writeln('You have just selected: '.$model);
            // $filesystem = new Filesystem();
            // $file = PhpFile::fromCode(file_get_contents('src/Entity/Organization/Corporation.php'));
            // $key = array_key_first($file->getClasses());
            // $class = $file->getClasses()[$key];
            // $class->addProperty('englishName')
            // ->setPrivate()
            // ->addComment('英文名称')
            // ->addComment('@ORM\Column(type="string", length=180)');

            // if (!$filesystem->exists($entity)) {
            //     $io->error("不存在此模型");
            // }

            // try {
            //     $filesystem->dumpFile($entity, $file);
            // } catch (IOExceptionInterface $exception) {
            //     $io->error("An error occurred while dumping your file at ".$exception->getPath());
            // }
        }

        // $filesystem = new Filesystem();
        // $entity = 'src/Entity/Organization/Corporation.php';

        // if ($input->getOption('test')) {
        //     // $class = ClassType::fromCode(file_get_contents('src/Entity/Organization/Corporation.php'));
        //     $file = PhpFile::fromCode(file_get_contents('src/Entity/Organization/Corporation.php'));
        //     $key = array_key_first($file->getClasses());
        //     $class = $file->getClasses()[$key];
        //     $class->addProperty('englishName')
        //     ->setPrivate()
        //     ->addComment('英文名称')
        //     ->addComment('@ORM\Column(type="string", length=180)');

        //     if (!$filesystem->exists($entity)) {
        //         $io->error("不存在此模型");
        //     }

        //     try {
        //         $filesystem->dumpFile($entity, $file);
        //     } catch (IOExceptionInterface $exception) {
        //         $io->error("An error occurred while dumping your file at ".$exception->getPath());
        //     }

        // }

        return Command::SUCCESS;
    }
}
