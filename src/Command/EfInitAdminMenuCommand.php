<?php

namespace App\Command;

use App\Entity\Admin\Menu;
use App\Repository\Admin\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ef:init-admin-menu',
    description: 'Add a short description for your command',
)]
class EfInitAdminMenuCommand extends Command
{
    private $menu;
    private $em;
    private $menuRepo;

    public function __construct($menuYaml, EntityManagerInterface $em, MenuRepository $menuRepo)
    {
        parent::__construct();
        $this->em = $em;
        $this->menu = $menuYaml;
        $this->menuRepo = $menuRepo;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'Menu Name')
            ->addArgument('parentName', InputArgument::OPTIONAL, 'Parent Menu Name')
            ->addOption('add', null, InputOption::VALUE_NONE, 'Add menu from command')
            ->addOption('yaml', null, InputOption::VALUE_NONE, 'Init menu from yaml file')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // $arg1 = $input->getArgument('arg1');

        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }

        if ($input->getOption('yaml')) {
            foreach($this->menu as &$menuItem) {
                $label = $menuItem[0];
                $icon = $menuItem[1];
                $uri = $menuItem[2];
                $parentMenu = $menuItem[3];

                $menuEntity = new Menu();
                $menuEntity->setLabel($label)
                  ->setIcon($icon)
                  ->setUri($uri);

                if ($label !== "root") {
                    $parent = $this->menuRepo->findOneBy(['label' => $parentMenu]);
                    $menuEntity->setParent($parent);
                }

                $this->em->persist($menuEntity);
                $this->em->flush();
            }
        }

        // 通过命令行增加菜单
        // if ($input->getOption('add')) {
        
        // }

        $io->success('操作菜单成功');

        return Command::SUCCESS;
    }
}
