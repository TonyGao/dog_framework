<?php

namespace App\EventSubscriber\Init;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class HeaderStateSubscriber implements EventSubscriberInterface
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        // 从 cookie 中获取 headerState, 默认值是 'expanded'
        $headerState = $request->cookies->get('headerState', 'expanded');

        // 将 headerState 传递到 Twig 模板中
        $this->twig->addGlobal('headerState', $headerState);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 100],
        ];
    }
}
