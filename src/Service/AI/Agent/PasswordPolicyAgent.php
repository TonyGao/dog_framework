<?php

namespace App\Service\AI\Agent;

use App\Service\AI\AiManager;
use Psr\Log\LoggerInterface;

class PasswordPolicyAgent
{
    private AiManager $aiManager;
    private LoggerInterface $logger;

    public function __construct(
        AiManager $aiManager,
        LoggerInterface $logger
    ) {
        $this->aiManager = $aiManager;
        $this->logger = $logger;
    }

    public function parsePolicyInstruction(string $naturalLanguage): array
    {
        $systemPrompt = <<<EOT
You are a security policy expert. Your task is to convert a natural language description of a password strength policy into a JSON DSL expression.

### 1. DATA SCHEMA
The available fields you can use in the DSL are:
- length (integer)
- has_number (boolean)
- has_lowercase (boolean)
- has_uppercase (boolean)
- has_special (boolean)
- has_case (string: "mixed" means both uppercase and lowercase)

Supported operators: >=, <=, =, !=

### 2. MODIFICATION RULES
- If the instruction mentions "at least X characters", use `{"field": "length", "operator": ">=", "value": X}`
- If it mentions "must contain numbers", use `{"field": "has_number", "operator": "=", "value": true}`
- If it mentions "must contain uppercase", use `{"field": "has_uppercase", "operator": "=", "value": true}`
- If it mentions "must contain lowercase", use `{"field": "has_lowercase", "operator": "=", "value": true}`
- If it mentions "must contain special characters", use `{"field": "has_special", "operator": "=", "value": true}`
- If it mentions "both uppercase and lowercase" or "mixed case", use `{"field": "has_case", "operator": "=", "value": "mixed"}`

If there are multiple conditions, wrap them in an "and" array. If there is only one condition, just return that condition.

### 3. RESPONSE FORMAT
Return ONLY a valid JSON object representing the DSL expression. Do not include markdown code blocks or any explanation text.

Example 1 (Single condition):
{"field": "length", "operator": ">=", "value": 6}

Example 2 (Multiple conditions):
{
  "and": [
    {"field": "length", "operator": ">=", "value": 8},
    {"field": "has_number", "operator": "=", "value": true}
  ]
}
EOT;

        $options = [
            'temperature' => 0.1,
            'response_format' => ['type' => 'json_object']
        ];

        try {
            $response = $this->aiManager->chat([
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $naturalLanguage]
            ], $options);

            // Clean up potential markdown blocks if the model ignored the instruction
            $response = preg_replace('/```json\s*/', '', $response);
            $response = preg_replace('/```\s*/', '', $response);
            $response = trim($response);

            $parsed = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('Failed to parse AI response as JSON: ' . json_last_error_msg(), ['response' => $response]);
                return ['error' => 'Failed to parse AI response.'];
            }

            return $parsed;
        } catch (\Exception $e) {
            $this->logger->error('Error calling AI for password policy: ' . $e->getMessage());
            return ['error' => 'AI Service unavailable.'];
        }
    }
}
