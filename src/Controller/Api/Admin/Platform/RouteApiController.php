<?php

namespace App\Controller\Api\Admin\Platform;

use App\Controller\Api\ApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\RouterInterface;

class RouteApiController extends AbstractController
{
  private $router;

  public function __construct(RouterInterface $router)
  {
    $this->router = $router;
  }

  #[Route(
    '/_symfony_routes',
    name: '_symfony_routes',
    methods: ['GET']
  )]
  public function getRoutes()
  {
    $routes = $this->router->getRouteCollection()->all();
    // 格式化路由信息
    $formattedRoutes = [];
    foreach ($routes as $name => $route) {
      // 只返回以 api 开头的路由
      // if (strpos($name, 'api') === 0 || substr($name, -5) === 'cache') {
      //     $formattedRoutes[$name] = [
      //         'path' => $route->getPath(),
      //         'methods' => $route->getMethods(),
      //     ];
      // }

      // 暂时全部开放给前端
      if (strpos($name, '_') !== 0) {
        $formattedRoutes[$name] = [
          'path' => $route->getPath(),
          'methods' => $route->getMethods(),
        ];
      }
    }

    return ApiResponse::success(json_encode($formattedRoutes));
  }
}
