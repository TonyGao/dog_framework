<?php

namespace App\Controller\Api\Admin\Platform;

use App\Service\Utils\AlimtTranslationService; // 假设您已有的翻译服务
use App\Controller\Api\ApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UtilsApiController extends AbstractController
{
    private AlimtTranslationService $translationService;

    public function __construct(AlimtTranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * 翻译接口
     */
    #[Route(
      '/api/admin/platform/utils/translate',
      name: 'api_platform_utils_translate',
      methods: ['POST']
    )]
    public function translate(Request $request): ApiResponse
    {
        // 获取翻译请求参数
        $data = $request->toArray();
        $sourceText = $data['sourceText'] ?? '';
        $sourceLanguage = $data['sourceLanguage'] ?? 'zh';
        $targetLanguage = $data['targetLanguage'] ?? 'en';

        // 检查是否包含中文字符
        $containsChinese = preg_match('/[\x{4e00}-\x{9fa5}]/u', $sourceText);
        
        // 如果源语言是中文但文本不包含中文字符，直接返回原文
        if ($sourceLanguage === 'zh' && !$containsChinese) {
            $jsonResponse = json_encode(['translatedText' => $sourceText]);
            return ApiResponse::success($jsonResponse, 'success', 'No translation needed');
        }

        try {
            // 调用翻译服务
            $text = $this->translationService->translate($sourceText, $sourceLanguage, $targetLanguage);
            $jsonResponse = json_encode(['translatedText' => $text]);
            return ApiResponse::success($jsonResponse, 'success', 'Translation successful');
        } catch (\Exception $e) {
            return ApiResponse::error('', '500', $e->getMessage());
        }
    }
}
