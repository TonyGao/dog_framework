<?php

namespace App\Service\AI;

use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\SystemMessage;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AiManager
{
    public function __construct(
        #[Autowire(service: 'ai.platform.generic.aliyun')]
        private PlatformInterface $aliyunPlatform,
        #[Autowire(service: 'ai.platform.generic.lmstudio')]
        private PlatformInterface $lmStudioPlatform,
        #[Autowire(env: 'AI_PROVIDER')]
        private string $defaultProviderName,
        #[Autowire(env: 'ALIYUN_MODEL_ID')]
        private string $aliyunModelId
    ) {}

    public function chat(array $messages, array $options = []): string
    {
        $provider = $this->defaultProviderName;
        
        if ($provider === 'lm_studio' || $provider === 'lmstudio') {
            $platform = $this->lmStudioPlatform;
            $model = 'local-model';
        } else {
            $platform = $this->aliyunPlatform;
            $model = $this->aliyunModelId;
        }

        $bag = new MessageBag();
        foreach ($messages as $msg) {
            $content = $msg['content'];
            if ($msg['role'] === 'system') {
                $bag->add(new SystemMessage($content));
            } elseif ($msg['role'] === 'user') {
                $bag->add(new UserMessage($content));
            } else {
                // Fallback for assistant or other roles if needed, currently assuming user/system for simple query
                $bag->add(new UserMessage($content));
            }
        }

        // Generic platform passes options directly to the client if configured? 
        // For generic platform, options might need to be passed in a specific way or it merges them.
        // But invoke() takes options.
        
        $result = $platform->invoke($model, $bag, $options);
        return $result->asText();
    }
}
