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
      // 只返回特定前缀的路由给前端
      $allowedPrefixes = ['api_', 'platform_'];
      $shouldInclude = false;
      
      // 检查路由名称是否以允许的前缀开头
      foreach ($allowedPrefixes as $prefix) {
        if (strpos($name, $prefix) === 0) {
          $shouldInclude = true;
          break;
        }
      }

      if ($shouldInclude) {
        $formattedRoutes[$name] = [
          'path' => $route->getPath(),
          'methods' => $route->getMethods(),
          'parameters' => $route->compile()->getPathVariables(),
        ];
      }
    }

    return ApiResponse::success(json_encode($formattedRoutes));
  }
}
