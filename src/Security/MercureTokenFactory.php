<?php

namespace App\Security;

use Firebase\JWT\JWT;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MercureTokenFactory
{
    private string $jwtSecret;

    public function __construct(
        #[Autowire('%env(MERCURE_JWT_SECRET)%')] string $jwtSecret
    ) {
        $this->jwtSecret = $jwtSecret;
    }

    /**
     * 生成包含订阅权限的 JWT
     *
     * @param array $topics 允许订阅的主题列表，支持通配符如 ['/user/123/*', '/notifications']
     * @param array $roles 附加的用户角色信息
     * @param int $expiresIn 过期时间，默认 1 小时
     * @return string
     */
    public function createSubscribeToken(array $topics, array $roles = [], int $expiresIn = 3600): string
    {
        $payload = [
            'mercure' => [
                'subscribe' => $topics,
                'payload'   => ['roles' => $roles]
            ],
            'exp' => time() + $expiresIn
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
}
