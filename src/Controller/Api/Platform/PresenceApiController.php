<?php

namespace App\Controller\Api\Platform;

use App\Controller\Api\ApiResponse;
use App\Service\Platform\PresenceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * 用户在线状态 API
 */
#[Route('/api/presence')]
#[IsGranted('ROLE_USER')]
class PresenceApiController extends AbstractController
{
    private PresenceService $presenceService;

    public function __construct(PresenceService $presenceService)
    {
        $this->presenceService = $presenceService;
    }

    /**
     * 心跳接口，更新用户在线状态
     */
    #[Route('/heartbeat', name: 'api_presence_heartbeat', methods: ['POST'])]
    public function heartbeat(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return ApiResponse::error('User not authenticated');
        }

        $this->presenceService->heartbeat($user->getUserIdentifier());

        return ApiResponse::success(json_encode([
            'status' => 'online',
            'timestamp' => time()
        ]));
    }

    /**
     * 批量检查用户在线状态
     */
    #[Route('/check', name: 'api_presence_check', methods: ['POST'])]
    public function check(array $userIds = []): Response
    {
        // 这里的 $userIds 可以从请求体中获取，为了演示简单起见
        $results = [];
        foreach ($userIds as $userId) {
            $results[$userId] = $this->presenceService->isOnline($userId);
        }

        return ApiResponse::success(json_encode($results));
    }
}
