<?php

namespace App\Service\Platform;

use App\Repository\Platform\MenuRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BreadcrumbService
{
    private $menuRepository;
    private $requestStack;
    private $urlGenerator;

    public function __construct(
        MenuRepository $menuRepository,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->menuRepository = $menuRepository;
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * 获取当前页面的面包屑路径
     *
     * @param string|null $currentRoute 当前路由名称
     * @param string|null $currentLabel 当前页面标题
     * @return array 面包屑项目数组
     */
    public function getBreadcrumbItems(?string $currentRoute = null, ?string $currentLabel = null): array
    {
        $request = $this->requestStack->getCurrentRequest();
        
        // 如果没有提供当前路由，则尝试从请求中获取
        if (!$currentRoute) {
            $currentRoute = $request->attributes->get('_route');
        }
        
        $breadcrumbItems = [];
        
        // 首页项
        $breadcrumbItems[] = [
            'label' => '首页',
            'url' => '/admin/index'
        ];
        
        // 查找当前菜单
        $currentMenu = $this->menuRepository->findOneBy(['routeName' => $currentRoute]);
        
        if ($currentMenu) {
            // 如果找到当前菜单，则构建面包屑路径
            $parentMenu = $currentMenu->getParent();
            $breadcrumbItems[] = ['label' => $currentMenu->getLabel()];
            
            // 递归查找所有父级菜单
            $parents = [];
            while ($parentMenu && $parentMenu->getLabel() != 'root') {
                $url = $parentMenu->getUri();
                if ($parentMenu->getRouteName()) {
                    try {
                        $url = $this->urlGenerator->generate($parentMenu->getRouteName());
                    } catch (\Exception $e) {
                        // 如果路由不存在，则使用URI
                        $url = $parentMenu->getUri();
                    }
                }
                
                $parents[] = [
                    'label' => $parentMenu->getLabel(),
                    'url' => $url
                ];
                
                $parentMenu = $parentMenu->getParent();
            }
            
            // 反转父级菜单顺序并添加到面包屑
            $parents = array_reverse($parents);
            array_splice($breadcrumbItems, 1, 0, $parents);
        } elseif ($currentLabel) {
            // 如果没有找到菜单项，则只显示当前页面标题
            $breadcrumbItems[] = ['label' => $currentLabel];
        }
        
        return $breadcrumbItems;
    }
}