<?php

namespace App\Service\Utils;

use AlibabaCloud\SDK\Alimt\V20181012\Alimt;
use AlibabaCloud\SDK\Alimt\V20181012\Models\TranslateGeneralRequest;
use AlibabaCloud\SDK\Alimt\V20181012\Models\TranslateGeneralResponse;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils;
use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;

class AlimtTranslationService
{
    private $client;

    public function __construct(array $alimtConfig)  // 接受整个配置
    {
        $config = new Config([
            'accessKeyId' => $alimtConfig['clients']['translation']['access_key_id'],
            'accessKeySecret' => $alimtConfig['clients']['translation']['access_key_secret'],
        ]);
        $config->endpoint = $alimtConfig['clients']['translation']['endpoint'];
        $this->client = new Alimt($config);
    }

    public function translate(string $sourceText, string $sourceLanguage = 'zh', string $targetLanguage = 'en'): string
    {
        $request = new TranslateGeneralRequest([
            'formatType' => 'text',
            'sourceLanguage' => $sourceLanguage,
            'targetLanguage' => $targetLanguage,
            'sourceText' => $sourceText,
            'scene' => 'general',
        ]);
        $runtime = new RuntimeOptions([]);

        try {
            // 返回 TranslateGeneralResponse 对象
            $response = $this->client->translateGeneralWithOptions($request, $runtime);
            // 访问翻译结果，假设路径是 body->data->translated
            return $response->body->data->translated;
        } catch (TeaError $error) {
            // 处理错误
            throw new \RuntimeException($error->message);
        }
    }
}

