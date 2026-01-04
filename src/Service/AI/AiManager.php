<?php

namespace App\Service\AI;

use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\SystemMessage;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\AI\Platform\Message\Content\Text;
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
        private string $aliyunModelId,
        #[Autowire(env: 'LM_STUDIO_MODEL_ID')]
        private string $lmStudioModelId = 'deepseek/deepseek-r1-0528-qwen3-8b'
    ) {}

    public function chat(array $messages, array $options = []): string
    {
        $provider = $this->defaultProviderName;
        
        if ($provider === 'lm_studio' || $provider === 'lmstudio') {
            $platform = $this->lmStudioPlatform;
            $model = $this->lmStudioModelId;
        } else {
            $platform = $this->aliyunPlatform;
            $model = $this->aliyunModelId;
        }

        if (isset($options['model'])) {
            $model = $options['model'];
            unset($options['model']);
        }

        $bag = new MessageBag();
        foreach ($messages as $msg) {
            $content = $msg['content'];
            if ($msg['role'] === 'system') {
                $bag->add(new SystemMessage($content));
            } elseif ($msg['role'] === 'user') {
                $bag->add(new UserMessage(new Text($content)));
            } else {
                // Fallback for assistant or other roles if needed, currently assuming user/system for simple query
                $bag->add(new UserMessage(new Text($content)));
            }
        }

        // Generic platform passes options directly to the client if configured? 
        // For generic platform, options might need to be passed in a specific way or it merges them.
        // But invoke() takes options.
        
        // Ensure stream is enabled for models that require it (like qwq-32b)
        $options['stream'] = true;
        
        $result = $platform->invoke($model, $bag, $options);
        
        if (isset($options['stream']) && $options['stream']) {
            try {
                $stream = $result->asStream();
                $fullText = '';
                foreach ($stream as $chunk) {
                    $fullText .= $chunk;
                }
                return $fullText;
            } catch (\Exception $e) {
                // Try asText() as fallback (e.g. if provider returned non-stream response)
                try {
                    return $result->asText();
                } catch (\Exception $ignored) {
                    // If fallback also fails, throw original exception
                    throw $e;
                }
            }
        }

        return $result->asText();
    }
}
