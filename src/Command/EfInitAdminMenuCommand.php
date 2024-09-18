<?php

namespace App\Command;

use App\Entity\Platform\Menu;
use App\Repository\Platform\MenuRepository;
use App\Service\Platform\MenuStaticGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Twig\Environment;

#[AsCommand(
    name: 'ef:init-admin-menu',
    description: 'Add a short description for your command',
)]
class EfInitAdminMenuCommand extends Command
{
    private $menu;
    private $em;
    private $menuRepo;
    private $twig;
    private $menuStaticGenerator;

    public function __construct(
        $menuYaml,
        EntityManagerInterface $em,
        MenuRepository $menuRepo,
        Environment $twig,
        MenuStaticGenerator $menuStaticGenerator,
    ) {
        parent::__construct();
        $this->em = $em;
        $this->menu = $menuYaml;
        $this->menuRepo = $menuRepo;
        $this->twig = $twig;
        $this->menuStaticGenerator = $menuStaticGenerator;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'Menu Name')
            ->addArgument('parentName', InputArgument::OPTIONAL, 'Parent Menu Name')
            ->addOption('add', null, InputOption::VALUE_NONE, 'Add menu from command')
            ->addOption('yaml', null, InputOption::VALUE_NONE, 'Init menu from yaml file')
            ->addOption('static', null, InputOption::VALUE_NONE, 'Make a static menu twig file');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('yaml')) {
            foreach ($this->menu as &$menuItem) {
                $label = $menuItem[0];
                $icon = $menuItem[1];
                $uri = $menuItem[2];
                $parentMenu = $menuItem[3];

                $isExitMenu = $this->menuRepo->findOneBy(['label' => $label]);
                if (!$isExitMenu) {
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
        }


        if ($input->getOption('static')) {
            $this->menuStaticGenerator->generateStaticMenu($io);
        }
        // if ($input->getOption('static')) {
        //     // Create a static menu twig file
        //     $filesystem = new Filesystem();
        //     $menuTwig = 'templates/admin/static/menu.html.twig';

        //     $repo = $this->em->getRepository(Menu::class);
        //     $root = $repo->childrenHierarchy();
        //     $root !== [] ? $menus = $root[0]['__children'] : $menus = [];
        //     $html = $this->twig->render('admin/dynamic/menu.html.twig', [
        //         'menus' => $menus
        //     ]);

        //     if (!$filesystem->exists($menuTwig)) {
        //         try {
        //             $filesystem->touch($menuTwig);
        //         } catch (IOExceptionInterface $exception) {
        //             $io->error("An error occurred while creating your directory at " . $exception->getPath());
        //         }
        //     }

        //     try {
        //         $filesystem->dumpFile($menuTwig, $html);
        //     } catch (IOExceptionInterface $exception) {
        //         $io->error("An error occurred while dumping your file at " . $exception->getPath());
        //     }
        // }

        // 通过命令行增加菜单
        // if ($input->getOption('add')) {

        // }

        $io->success('操作菜单成功');

        return Command::SUCCESS;
    }
}
