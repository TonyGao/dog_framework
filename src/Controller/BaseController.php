<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Config\Path;

class BaseController extends AbstractController
{
  /**
   * 动态调用不可见或不存在的 "_get" 前缀方法
   *
   * 该方法实现了魔术方法 `__call`，用于捕获对类中不存在的方法的调用。
   * 当调用以 "_get" 开头的方法时，该方法会自动解析对应的常量值。
   * 例如，调用 `_getSomeConstant()` 将尝试返回 `Path::SOME_CONSTANT` 常量的值。
   *
   * @param string $method 调用的方法名称
   * @param array $arguments 调用方法时传递的参数
   * 
   * @return mixed 如果常量定义存在，则返回对应的常量值
   * @throws \BadMethodCallException 当调用的方法不存在或常量未定义时抛出异常
   */
  public function __call($method, $arguments)
  {
      // 检查方法名是否以 "_get" 开头
      if (strpos($method, '_get') === 0) {
          // 获取常量名称，去掉 "_get" 前缀
          $constantName = substr($method, 4);
          
          // 检查 Path 类中是否定义了该常量
          if (defined(Path::class . '::' . $constantName)) {
              // 返回对应常量的值
              return constant(Path::class . '::' . $constantName);
          }
      }

      // 如果方法不存在或常量未定义，抛出 BadMethodCallException
      throw new \BadMethodCallException(sprintf(
          'Call to undefined method %s::%s',
          static::class,
          $method
      ));
  }
}
