<?php

namespace App\Service\AI\Agent;

use App\Service\AI\AiManager;
use App\Service\Platform\DataGridService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class NaturalLanguageQueryAgent
{
    private AiManager $aiManager;
    private DataGridService $dataGridService;
    private LoggerInterface $logger;
    private string $provider;
    private ?string $aliyunFastModelId;

    public function __construct(
        AiManager $aiManager, 
        DataGridService $dataGridService,
        LoggerInterface $logger,
        #[Autowire(env: 'AI_PROVIDER')] string $provider,
        #[Autowire(env: 'ALIYUN_FAST_MODEL_ID')] ?string $aliyunFastModelId = null
    ) {
        $this->aiManager = $aiManager;
        $this->dataGridService = $dataGridService;
        $this->logger = $logger;
        $this->provider = $provider;
        $this->aliyunFastModelId = $aliyunFastModelId;
    }

    public function parseQuery(string $text, string $entityClass, array $currentFilters = []): array
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
                
                // HINT for Relation Fields
                // If the field name suggests a relation (no "id" suffix, but likely stores an ID), 
                // prompt the AI to use dot notation for text filtering.
                if (in_array($col['field'], ['department', 'company', 'parent', 'owner', 'manager', 'createdBy', 'updatedBy'])) {
                     $schemaDesc[] = "  (Note: `{$col['field']}` is a relation ID. To filter by Name/Title, use `{$col['field']}.name` or `{$col['field']}.title`)";
                }
            }
        }
        $schemaString = implode("\n", $schemaDesc);

        // 2. Build Prompt
        $systemPrompt = <<<EOT
You are a JSON List Editor and Query Optimizer.
Your task is to MODIFY an existing list of filters based on a natural language instruction.

---
### 1. DATA SCHEMA
The available fields are:
$schemaString

Supported operators: equals, not_equals, contains, not_contains, begins_with, ends_with, greater_than, less_than, greater_than_or_equal, less_than_or_equal, is_null, is_not_null, in.

**CRITICAL RULE FOR RELATIONS:**
If the user filters by a related entity (e.g. Department, Company, User) using its **Name** or **Title** (text), you **MUST** use dot notation to target the text field.
*   **CORRECT**: `department.name`, `company.name`, `createdBy.name`
*   **INCORRECT**: `department`, `company` (These are ID fields and will cause DB errors if used with text values)

---
### 2. INPUT CONTEXT
**Current JSON List:**
%CURRENT_FILTERS%

**User Instruction:**
"$text"

---
### 3. MODIFICATION RULES (CRITICAL)
You MUST return the **FULL** list of filters, including any that were NOT modified.
Do NOT just return the new condition.

**Logic for updates:**
1.  **Adding a condition for a NEW field**:
    *   Append it to the list (Implicit AND).
    *   **EXCEPTION**: If the user explicitly asks for "OR" (e.g. "or state is disabled"), you MUST create a Logical Group with `logic: "OR"`.
        *   Structure: `{ "logic": "OR", "filters": [ ...existing items..., ...new item... ] }`
2.  **Adding a condition for an EXISTING field**:
    *   **MERGE** it with the existing filter using **OR** logic.
    *   *Example*: If `status=active` exists and user says "also pending", change to `OR(status=active, status=pending)`.
    *   **IMPORTANT**: Remove the old single filter when creating a group.
3.  **Refining/Replacing**:
    *   If the user says "change X to Y", replace the value.
    *   If the user says "remove X", remove it.

---
### 4. RESPONSE FORMAT
Return a JSON object with two keys. 
**IMPORTANT**: Do NOT wrap the response in another 'filters' object. The top-level keys must be 'thought_process' and 'filters'.

1.  `thought_process`: A brief explanation of your action.
2.  `filters`: The final complete array of filter objects.

**Example Response:**
{
  "thought_process": "Merging new value...",
  "filters": [
    { "field": "name", "operator": "equals", "value": "A" }
  ]
}
EOT;

        $currentFiltersJson = empty($currentFilters) ? '[]' : json_encode($currentFilters, JSON_UNESCAPED_UNICODE);
        $systemPrompt = str_replace('%CURRENT_FILTERS%', $currentFiltersJson, $systemPrompt);

        // 3. Call AI
        $options = [
            'temperature' => 0.1,
            // 'response_format' => ['type' => 'json_object'] // Removed to avoid compatibility issues with some models (e.g. qwq-32b)
        ];

        // Optimize for speed: use faster model for Aliyun (if default is reasoning model)
        if ($this->provider === 'aliyun' && $this->aliyunFastModelId) {
            // Use configured fast model (e.g. 'qwen-plus') instead of reasoning model (e.g. 'qwq-32b')
            $options['model'] = $this->aliyunFastModelId;
        }

        $response = $this->aiManager->chat([
            ['role' => 'system', 'content' => 'You are a helpful assistant that outputs JSON.'],
            ['role' => 'user', 'content' => $systemPrompt]
        ], $options);

        // 4. Parse
        $response = trim($response);
        
        // Remove <think>...</think> blocks (DeepSeek style)
        // Strategy: if </think> is present, take everything after the last occurrence
        if (($pos = strrpos($response, '</think>')) !== false) {
             $response = substr($response, $pos + strlen('</think>'));
        } else {
             // Fallback: try regex for standard <think> removal
             $response = preg_replace('/<think>[\s\S]*?<\/think>/i', '', $response);
        }
        
        // Extract JSON part: look for object '{' first (since we now expect an object wrapper), or array '['
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        
        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
             $cleanJson = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
        } else {
             // Fallback to array if object not found (legacy behavior)
             $start = strpos($response, '[');
             $end = strrpos($response, ']');
             if ($start !== false && $end !== false && $end > $start) {
                $cleanJson = substr($response, $start, $end - $start + 1);
             } else {
                $cleanJson = preg_replace('/^```json\s*|\s*```$/', '', $response);
             }
        }
        
        try {
            $data = json_decode($cleanJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            // Attempt to recover from "extra closing brace" or trailing garbage
            // which often happens with some models.
            // Search backwards for the previous '}'
            $currentEnd = $jsonEnd;
            $recovered = false;
            
            while (true) {
                // Search in space BEFORE currentEnd
                $searchSpace = substr($response, 0, $currentEnd);
                $nextEnd = strrpos($searchSpace, '}');
                
                if ($nextEnd === false || $nextEnd <= $jsonStart) {
                    break;
                }
                
                $candidate = substr($response, $jsonStart, $nextEnd - $jsonStart + 1);
                try {
                    $data = json_decode($candidate, true, 512, JSON_THROW_ON_ERROR);
                    $recovered = true;
                    $this->logger->warning("Recovered from JSON parse error by backtracking", ['original_error' => $e->getMessage()]);
                    break;
                } catch (\JsonException $e2) {
                    $currentEnd = $nextEnd;
                }
            }

            if (!$recovered) {
                $this->logger->error("AI JSON Parse Error: " . $e->getMessage(), ['response' => $response]);
                throw new \RuntimeException("Failed to parse AI response.");
            }
        }

        $this->logger->info("Parsed AI Response", ['data' => $data]);
            
        // Check for new "thought_process" wrapper
        if (isset($data['thought_process'])) {
            // Log the thought process for debugging
            $this->logger->info("AI Thought Process: " . $data['thought_process']);
        }

        // Handle case where LLM returns object with "filters" key instead of array
        // Use recursive extraction to handle any level of nesting
        $extracted = $this->extractFiltersAndThoughtProcess($data);
        
        $this->logger->info("Extracted Filters", ['extracted' => $extracted]);

        // Safety Fix: Ensure relations are using dot notation for text values
        if (!empty($extracted['filters'])) {
            $extracted['filters'] = $this->fixRelationFilters($extracted['filters']);
        }

        if (!empty($extracted['filters'])) {
            return $extracted;
        }

        // Fallback for legacy simple list
        if (is_array($data)) {
            // Check if it's an associative array (single object) or list
            if (array_keys($data) !== range(0, count($data) - 1)) {
                    // Check if it HAS 'filters' key but we missed it?
                    if (isset($data['filters'])) {
                        // This should have been caught by extractFiltersAndThoughtProcess
                        // but let's be safe.
                        return $this->extractFiltersAndThoughtProcess($data['filters']);
                    }
                // It's a single object (and not the wrapper we expected), wrap it
                return ['filters' => [$data], 'thought_process' => null];
            }
            return ['filters' => $data, 'thought_process' => null];
        }
        
        return ['filters' => [], 'thought_process' => null];
    }

    /**
     * Recursively fix relation filters (e.g. department="Marketing" -> department.name="Marketing")
     */
    private function fixRelationFilters(array $filters): array
    {
        $relationFields = ['department', 'company', 'parent', 'owner', 'manager', 'createdBy', 'updatedBy'];
        
        foreach ($filters as &$item) {
            // Handle Logic Groups Recursively
            if (isset($item['logic']) && isset($item['filters']) && is_array($item['filters'])) {
                $item['filters'] = $this->fixRelationFilters($item['filters']);
                continue;
            }
            
            // Handle Simple Conditions
            if (isset($item['field']) && isset($item['value'])) {
                // If field is a relation AND value is NOT a UUID (it's text/chinese/etc)
                // Then append .name
                if (in_array($item['field'], $relationFields)) {
                     $val = $item['value'];
                     // Simple UUID check: 36 chars containing dashes
                     $isUuid = is_string($val) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $val);
                     
                     if (!$isUuid) {
                         $item['field'] = $item['field'] . '.name';
                         $this->logger->info("Auto-fixed relation filter", ['original' => $item['field'], 'new' => $item['field'] . '.name', 'value' => $val]);
                     }
                }
            }
        }
        
        return $filters;
    }

    /**
     * Recursively extract filters and thought_process from nested structures.
     */
    private function extractFiltersAndThoughtProcess(array $data, ?string $parentThoughtProcess = null): array
    {
        $thoughtProcess = $data['thought_process'] ?? $parentThoughtProcess;
        // $thoughtProcess = "[DEBUG: Unwrapping...] " . $thoughtProcess; // Uncomment to debug

        // 1. Is the current object ITSELF a Logical Group? (Has logic + filters)
        // This handles case where AI returns a single Group object as root
        if (isset($data['logic']) && isset($data['filters']) && is_array($data['filters'])) {
             $group = $data;
             unset($group['thought_process']); // Clean up
             return [
                 'filters' => [$group],
                 'thought_process' => $thoughtProcess
             ];
        }

        // 2. If 'filters' key exists
        if (isset($data['filters'])) {
            $filters = $data['filters'];
            
            // If filters is an array (list or object)
            if (is_array($filters)) {
                // Check if it is a LIST (numeric keys)
                if (empty($filters) || array_keys($filters) === range(0, count($filters) - 1)) {
                    // It's a list. Assume it's the filter list.
                    return [
                        'filters' => $filters,
                        'thought_process' => $thoughtProcess
                    ];
                }
                
                // It is an associative array (object).
                // Check if it is a Logical Group?
                if (isset($filters['logic']) && isset($filters['filters'])) {
                     // It IS a logical group. Wrap it.
                     return [
                         'filters' => [$filters],
                         'thought_process' => $thoughtProcess
                     ];
                }
                
                // It is a Wrapper (hallucination). Recurse!
                return $this->extractFiltersAndThoughtProcess($filters, $thoughtProcess);
            }
        }
        
        // 3. If 'filters' key does NOT exist, maybe THIS array IS the list?
        // Check if it's a list (numeric keys)
        if (array_keys($data) === range(0, count($data) - 1)) {
             // It's a list. Assume it's the filters.
             return [
                 'filters' => $data,
                 'thought_process' => $thoughtProcess
             ];
        }
        
        // 4. It's an object but no 'filters' key. 
        // Maybe it's a single filter object? (has 'field', 'operator', 'value')
        if (isset($data['field']) && isset($data['operator'])) {
             return [
                 'filters' => [$data],
                 'thought_process' => $thoughtProcess
             ];
        }

        // Nothing found
        return ['filters' => [], 'thought_process' => $thoughtProcess];
    }
}
