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
    description: 'Generate system menus through the YAML file and staticize it by generating a Twig file.',
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
            ->addOption('static', null, InputOption::VALUE_NONE, 'Make a static menu twig file')
            ->addOption('force', 'f', InputOption::VALUE_NONE, '强制覆盖现有菜单数据');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('yaml')) {
            // 检查是否使用强制覆盖选项
            $force = $input->getOption('force');
            
            // 如果使用了强制覆盖选项，先清空现有菜单数据
            if ($force) {
                $existingMenus = $this->menuRepo->findAll();
                if (count($existingMenus) > 0) {
                    $io->info('正在清空现有菜单数据...');
                    foreach ($existingMenus as $menu) {
                        $this->em->remove($menu);
                    }
                    $this->em->flush();
                    $io->success('现有菜单数据已清空');
                }
            }
            
            foreach ($this->menu as &$menuItem) {
                $label = $menuItem[0];
                $icon = $menuItem[1];
                $uri = $menuItem[2];
                $parentMenu = $menuItem[3];

                $isExitMenu = $this->menuRepo->findOneBy(['label' => $label]);
                if (!$isExitMenu || $force) {
                    // 如果菜单已存在且使用了强制选项，则更新而不是创建新的
                    if ($isExitMenu && $force) {
                        $menuEntity = $isExitMenu;
                        $menuEntity->setIcon($icon)
                            ->setUri($uri);
                    } else {
                        $menuEntity = new Menu();
                        $menuEntity->setLabel($label)
                            ->setIcon($icon)
                            ->setUri($uri);
                    }

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

        $io->success('操作菜单成功');
        return Command::SUCCESS;
    }
}
