<?php

namespace App\Service\AI\Provider;

use App\Service\AI\AiProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractOpenAiProvider implements AiProviderInterface
{
    protected HttpClientInterface $httpClient;
    protected LoggerInterface $logger;
    protected string $apiKey;
    protected string $baseUrl;
    protected string $defaultModel;

    public function __construct(
        HttpClientInterface $httpClient, 
        LoggerInterface $logger,
        string $apiKey,
        string $baseUrl,
        string $defaultModel
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
        $this->defaultModel = $defaultModel;
    }

    public function chat(array $messages, array $options = []): string
    {
        $model = $options['model'] ?? $this->defaultModel;
        $temperature = $options['temperature'] ?? 0.7;
        
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
        ];

        if (isset($options['response_format'])) {
            $payload['response_format'] = $options['response_format'];
        }

        try {
            $response = $this->httpClient->request('POST', $this->baseUrl . '/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 60,
            ]);

            $content = $response->toArray();
            
            if (isset($content['choices'][0]['message']['content'])) {
                return $content['choices'][0]['message']['content'];
            }
            
            $this->logger->error('AI Provider Unexpected Response', ['response' => $content]);
            throw new \RuntimeException('Unexpected response format from AI provider');
            
        } catch (\Exception $e) {
            $this->logger->error('AI Provider Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
