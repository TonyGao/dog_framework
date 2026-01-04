<?php

namespace App\Service\AI\Agent;

use App\Service\AI\AiManager;

class CodingAgent
{
    public function __construct(private AiManager $aiManager) {}

    public function generateCode(string $requirement): string
    {
        $messages = [
            ['role' => 'system', 'content' => 'You are an expert software developer. Generate clean, efficient code.'],
            ['role' => 'user', 'content' => $requirement]
        ];
        
        return $this->aiManager->chat($messages);
    }
}
