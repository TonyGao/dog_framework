<?php

namespace App\EventListener\Controller;

use App\Service\Controller\Common;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class AutoInjectCommonListener
{
  private $c;

  public function __construct(Common $c)
  {
    $this->c = $c;
  }

  public function onKernelController(ControllerEvent $event)
  {
    $controller = $event->getController();

    if (is_array($controller)) {
      $controllerObject = $controller[0];

      $controllerObject->c = $this->c;
    }
  }
}