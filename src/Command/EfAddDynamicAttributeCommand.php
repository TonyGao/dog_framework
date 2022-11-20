<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'ef:addDynamicAttribute',
    description: 'Add a dynamic attribute to an entity',
)]
class EfAddDynamicAttributeCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('test', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }

        $filesystem = new Filesystem();
        $entity = 'src/Entity/Organization/Corporation.php';

        if ($input->getOption('test')) {
            // $class = ClassType::fromCode(file_get_contents('src/Entity/Organization/Corporation.php'));
            $file = PhpFile::fromCode(file_get_contents('src/Entity/Organization/Corporation.php'));
            $key = array_key_first($file->getClasses());
            $class = $file->getClasses()[$key];
            $class->addProperty('englishName')
            ->setPrivate()
            ->addComment('英文名称')
            ->addComment('@ORM\Column(type="string, lenght=180")');

            if (!$filesystem->exists($entity)) {
                $io->error("不存在此模型");
            }

            try {
                $filesystem->dumpFile($entity, $file);
            } catch (IOExceptionInterface $exception) {
                $io->error("An error occurred while dumping your file at ".$exception->getPath());
            }

        }

        $io->success('属性添加成功');

        return Command::SUCCESS;
    }
}
