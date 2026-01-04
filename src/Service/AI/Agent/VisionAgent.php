<?php

namespace App\Service\AI\Agent;

use App\Service\AI\AiManager;

class VisionAgent
{
    public function __construct(private AiManager $aiManager) {}

    public function analyzeImage(string $imageUrl, string $prompt): string
    {
        // TODO: Implement image analysis using AiManager or direct Platform access
        // Currently AiManager.chat only supports text, would need extension for Multimodal
        return "Image analysis not yet implemented.";
    }
}
