<?php

namespace App\Controller\Api\Platform;

use App\Controller\Api\ApiResponse;
use App\Service\Platform\PresenceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Request;

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
    public function heartbeat(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return ApiResponse::error('User not authenticated');
        }

        // 立即关闭并保存 Session，释放 Session 锁，提升后续请求并发能力
        if ($request->hasSession()) {
            $request->getSession()->save();
        }

        $this->presenceService->heartbeat($user->getUserIdentifier());

        return ApiResponse::success(json_encode([
            'status' => 'online',
            'timestamp' => time()
        ]));
    }

    /**
     * SSE 持续心跳，通过长连接维持在线状态
     * 优势：开发环境下节省网络请求面板空间，实时性高
     * 注意：每个连接会占用一个 PHP Worker 线程，高并发生产环境建议切换至 AJAX 模式
     */
    #[Route('/stream', name: 'api_presence_stream', methods: ['GET'])]
    public function presenceStream(Request $request): StreamedResponse
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $userId = $user->getUserIdentifier();
        
        // 关键：在进入长连接之前关闭 Session，释放 Session 锁
        if ($request->hasSession()) {
            $request->getSession()->save();
        }

        $response = new StreamedResponse(function () use ($userId) {
            set_time_limit(0);
            
            // 设置心跳更新频率（秒）
            $interval = 25;
            
            $this->presenceService->heartbeat($userId);
            echo "data: " . json_encode(['type' => 'open', 'status' => 'connected', 'timestamp' => time()]) . "\n\n";
            if (ob_get_level() > 0) ob_flush();
            flush();

            while (true) {
                if (connection_aborted()) {
                    break;
                }

                $this->presenceService->heartbeat($userId);
                echo "data: " . json_encode(['type' => 'ping', 'timestamp' => time()]) . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();

                sleep($interval);
            }
            
            try {
                $this->presenceService->markOffline($userId);
            } catch (\Exception $e) {}
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * 批量检查用户在线状态
     */
    #[Route('/check', name: 'api_presence_check', methods: ['POST'])]
    public function check(Request $request): Response
    {
        $body = json_decode($request->getContent(), true);
        $userIds = $body['userIds'] ?? [];
        $results = [];
        foreach ($userIds as $userId) {
            $results[$userId] = $this->presenceService->isOnline($userId);
        }

        return ApiResponse::success(json_encode($results));
    }
}
