<?php

namespace App\Service\AI\Agent;

use App\Service\AI\AiManager;
use App\Service\Platform\DataGridService;
use Psr\Log\LoggerInterface;

class NaturalLanguageQueryAgent
{
    private AiManager $aiManager;
    private DataGridService $dataGridService;
    private LoggerInterface $logger;

    public function __construct(
        AiManager $aiManager, 
        DataGridService $dataGridService,
        LoggerInterface $logger
    ) {
        $this->aiManager = $aiManager;
        $this->dataGridService = $dataGridService;
        $this->logger = $logger;
    }

    public function parseQuery(string $text, string $entityClass): array
    {
        // 1. Get Schema
        $dataGrid = $this->dataGridService->findDataGridByEntityClass($entityClass);
        if (!$dataGrid) {
            throw new \InvalidArgumentException("No DataGrid found for entity: $entityClass");
        }

        $config = $dataGrid->getDefaultConfigData();
        $columns = $config['columns'] ?? [];
        
        $schemaDesc = [];
        foreach ($columns as $col) {
            if (isset($col['field'])) {
                $label = $col['label'] ?? $col['field'];
                $type = $col['type'] ?? 'string';
                $schemaDesc[] = "- Field: {$col['field']} (Type: $type, Label: $label)";
            }
        }
        $schemaString = implode("\n", $schemaDesc);

        // 2. Build Prompt
        $systemPrompt = <<<EOT
You are an expert SQL/Doctrine query parser. Your goal is to translate natural language user queries into a structured JSON format for filtering a dataset.

The available fields are:
$schemaString

Supported operators: equals, not_equals, contains, not_contains, begins_with, ends_with, greater_than, less_than, greater_than_or_equal, less_than_or_equal, is_null, is_not_null, in.

Output Format:
Return ONLY a valid JSON array of objects. No markdown, no explanations.
Example: [{"field": "name", "operator": "contains", "value": "Manager"}, {"field": "department.name", "operator": "equals", "value": "IT"}]

If the user query implies a "relation" field (e.g. "IT Department"), map it to the correct field path (e.g. department.name if appropriate, or department).

User Query: "$text"
EOT;

        // 3. Call AI
        $response = $this->aiManager->chat([
            ['role' => 'system', 'content' => 'You are a helpful assistant that outputs JSON.'],
            ['role' => 'user', 'content' => $systemPrompt]
        ], [
            'temperature' => 0.1,
            'response_format' => ['type' => 'json_object'] // OpenAI specific, might need adjustment for others
        ]);

        // 4. Parse
        // Clean markdown code blocks if present (common with some LLMs)
        $cleanJson = preg_replace('/^```json\s*|\s*```$/', '', trim($response));
        
        try {
            $data = json_decode($cleanJson, true, 512, JSON_THROW_ON_ERROR);
            
            // Handle case where LLM returns object with "filters" key instead of array
            if (isset($data['filters']) && is_array($data['filters'])) {
                return $data['filters'];
            }
            if (is_array($data)) {
                // Check if it's an associative array (single object) or list
                if (array_keys($data) !== range(0, count($data) - 1)) {
                    // It's a single object, wrap it
                    return [$data];
                }
                return $data;
            }
            
            return [];
        } catch (\JsonException $e) {
            $this->logger->error("AI JSON Parse Error: " . $e->getMessage(), ['response' => $response]);
            throw new \RuntimeException("Failed to parse AI response.");
        }
    }
}
