<?php

namespace App\Controller\Admin;

use App\Service\AI\AgentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/admin/ai')]
class AiSearchController extends AbstractController
{
    public function __construct(
        private AgentManager $agentManager,
        private LoggerInterface $logger
    ) {}

    #[Route('/parse-policy', name: 'api_admin_ai_parse_policy', methods: ['POST'])]
    public function parsePolicy(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $text = $data['text'] ?? '';

            if (empty($text)) {
                return new JsonResponse(['error' => 'Missing text instruction'], 400);
            }

            $agent = $this->agentManager->getPasswordPolicyAgent();
            $result = $agent->parsePolicyInstruction($text);

            if (isset($result['error'])) {
                return new JsonResponse($result, 500);
            }

            return new JsonResponse([
                'expression' => $result
            ]);
        } catch (\Exception $e) {
            $this->logger->error('AI Parse Policy Error: ' . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/parse-query', name: 'api_admin_ai_parse_query', methods: ['POST'])]
    public function parseQuery(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $text = $data['text'] ?? '';
            $entityClass = $data['entity'] ?? '';
            $currentFilters = $data['currentFilters'] ?? [];

            if (empty($text) || empty($entityClass)) {
                return new JsonResponse(['error' => 'Missing text or entity'], 400);
            }

            // Call Agent
            $result = $this->agentManager->parseQuery($text, $entityClass, $currentFilters);

            // Handle new return structure (array with filters and thought_process)
            // or legacy structure (just filters array)
            if (isset($result['filters'])) {
                $filters = $result['filters'];
                $thoughtProcess = $result['thought_process'] ?? null;
            } else {
                $filters = $result;
                $thoughtProcess = null;
            }

            return new JsonResponse([
                'filters' => $filters,
                'thought_process' => $thoughtProcess
            ]);
        } catch (\Exception $e) {
            $this->logger->error('AI Parse Query Error: ' . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
