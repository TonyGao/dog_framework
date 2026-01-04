<?php

namespace App\Service\AI;

use App\Service\AI\Agent\NaturalLanguageQueryAgent;
use App\Service\AI\Agent\VisionAgent;
use App\Service\AI\Agent\CodingAgent;

class AgentManager
{
    public function __construct(
        private NaturalLanguageQueryAgent $queryAgent,
        private VisionAgent $visionAgent,
        private CodingAgent $codingAgent
    ) {}

    public function getQueryAgent(): NaturalLanguageQueryAgent
    {
        return $this->queryAgent;
    }

    public function getVisionAgent(): VisionAgent
    {
        return $this->visionAgent;
    }

    public function getCodingAgent(): CodingAgent
    {
        return $this->codingAgent;
    }

    // Facade methods
    public function parseQuery(string $text, string $entity, array $currentFilters = []): array
    {
        return $this->queryAgent->parseQuery($text, $entity, $currentFilters);
    }
}
