<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Config\Path;

class BaseController extends AbstractController
{
  public function __call($method, $arguments)
  {
      if (strpos($method, '_get') === 0) {
          $constantName = substr($method, 4);
          if (defined(Path::class . '::' . $constantName)) {
              return constant(Path::class . '::' . $constantName);
          }
      }

      throw new \BadMethodCallException(sprintf(
          'Call to undefined method %s::%s',
          static::class,
          $method
      ));
  }
}