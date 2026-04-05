<?php

namespace App\EventSubscriber;

use App\Security\MercureTokenFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MercureCookieSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;
    private MercureTokenFactory $mercureTokenFactory;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        MercureTokenFactory $mercureTokenFactory
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->mercureTokenFactory = $mercureTokenFactory;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        // 确保只在主请求时设置 Cookie
        if (!$event->isMainRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token || !$token->getUser()) {
            // 如果用户未登录且存在 Cookie，可以选择清除，但为了简单，这里依赖框架自带的 Session 退出机制
            return;
        }

        $user = $token->getUser();
        // 获取用户 ID
        $userId = method_exists($user, 'getId') ? (string)$user->getId() : $user->getUserIdentifier();

        // 允许订阅的 Topics：
        // 1. 全局通知： /notifications/global
        // 2. 当前用户的私有通道： https://enterprise.local/user/{id}/export (以及其他私有 topic)
        $topics = [
            'https://enterprise.local/user/' . $userId . '/export',
            '/user/' . $userId . '/*',
            '/entity/*', // 允许同步实体变更
        ];

        // 签发 JWT
        $jwt = $this->mercureTokenFactory->createSubscribeToken($topics);

        // 将 JWT 写入名为 mercureAuthorization 的 Cookie 中
        $cookie = Cookie::create('mercureAuthorization')
            ->withValue($jwt)
            ->withPath('/')
            ->withSecure($event->getRequest()->isSecure())
            ->withHttpOnly(false) // EventSource 需要携带 Cookie，根据场景调整，本地开发可先 false
            ->withSameSite(Cookie::SAMESITE_STRICT);

        $event->getResponse()->headers->setCookie($cookie);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
