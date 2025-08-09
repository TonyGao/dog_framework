<?php

namespace App\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Symfony\Bridge\Twig\DataCollector\TwigDataCollector;

class AssetDuplicationCollector extends DataCollector implements LateDataCollectorInterface
{
    private ?TwigDataCollector $twigCollector;
    private ?Request $request = null;
    private ?Response $response = null;

    public function __construct(?TwigDataCollector $twigCollector = null)
    {
        $this->twigCollector = $twigCollector;
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null)
    {
        // 存储请求和响应，在 lateCollect 中处理
        $this->request = $request;
        $this->response = $response;
    }

    public function lateCollect(): void
    {
        if (!$this->request || !$this->response) {
            return;
        }

        $html = $this->response->getContent();
        $requestPath = $this->request->getPathInfo();

        // 获取 Twig 模板信息
        $twigTemplates = $this->getTwigTemplates();

        $cssAssets = $this->extractAssetsWithContext($html, 'link', 'href', $requestPath, $twigTemplates);
        $jsAssets = $this->extractAssetsWithContext($html, 'script', 'src', $requestPath, $twigTemplates);

        $this->data['duplications'] = [
            'css' => $this->findDuplicatesWithContext($cssAssets),
            'js' => $this->findDuplicatesWithContext($jsAssets),
        ];
    }

    private function getTwigTemplates(): array
    {
        if (!$this->twigCollector) {
            return [];
        }

        try {
            // 直接使用 TwigDataCollector 的 getTemplates 方法
            return $this->twigCollector->getTemplates();
        } catch (\Exception $e) {
            // 如果失败，尝试从 Profile 中提取
            try {
                $profile = $this->twigCollector->getProfile();
                $templates = [];
                $this->extractTemplatesFromProfile($profile, $templates);
                return $templates;
            } catch (\Exception $e2) {
                return [];
            }
        }
    }

    /**
     * 从 Profile 对象中提取模板信息
     */
    private function extractTemplatesFromProfile($profile, array &$templates): void
    {
        if (!$profile) {
            return;
        }

        if ($profile->isTemplate()) {
            $templateName = $profile->getTemplate();
            if (!isset($templates[$templateName])) {
                $templates[$templateName] = 0;
            }
            $templates[$templateName]++;
        }

        // 递归处理子 Profile
        foreach ($profile as $childProfile) {
            $this->extractTemplatesFromProfile($childProfile, $templates);
        }
    }

    private function extractAssetsWithContext(string $html, string $tag, string $attr, string $requestPath, array $twigTemplates = []): array
    {
        $pattern = sprintf('/<%s[^>]*\s%s=["\']([^"\'>]+)["\'][^>]*>/i', $tag, $attr);
        preg_match_all($pattern, $html, $matches, PREG_OFFSET_CAPTURE);
        
        $assets = [];
        $assetOccurrenceCount = []; // 跟踪每个资源的出现次数
        $templateOccurrenceCount = []; // 跟踪每个模板中资源的出现次数
        
        foreach ($matches[1] as $index => $match) {
            $url = preg_replace('/\?.*/', '', $match[0]); // 去掉版本参数
            $fullMatch = $matches[0][$index][0];
            $position = $matches[0][$index][1];
            
            // 跟踪资源出现次数
            if (!isset($assetOccurrenceCount[$url])) {
                $assetOccurrenceCount[$url] = 0;
            }
            $assetOccurrenceCount[$url]++;
            $occurrenceIndex = $assetOccurrenceCount[$url];
            
            // 尝试找到周围的 Twig 注释来确定模板来源
            $context = $this->findTemplateContext($html, $position, $requestPath, $twigTemplates);
            
            // 尝试获取模板中的真实行号
            $templateLineNumber = null;
            if ($context) {
                // 为每个模板单独计算资源出现次数
                $templateKey = $context . '|' . $url;
                if (!isset($templateOccurrenceCount[$templateKey])) {
                    $templateOccurrenceCount[$templateKey] = 0;
                }
                $templateOccurrenceCount[$templateKey]++;
                $templateSpecificOccurrenceIndex = $templateOccurrenceCount[$templateKey];
                
                $templateLineNumber = $this->getTemplateLineNumber($url, $context, $templateSpecificOccurrenceIndex);
                // 如果模板行号获取失败，可能是模板名称不正确，尝试更精确的模板识别
                if ($templateLineNumber === null) {
                    $betterContext = $this->findMorePreciseTemplateContext($html, $position, $url);
                    if ($betterContext && $betterContext !== $context) {
                        // 重新计算在更精确模板中的出现次数
                        $betterTemplateKey = $betterContext . '|' . $url;
                        if (!isset($templateOccurrenceCount[$betterTemplateKey])) {
                            $templateOccurrenceCount[$betterTemplateKey] = 0;
                        }
                        $templateOccurrenceCount[$betterTemplateKey]++;
                        $betterTemplateOccurrenceIndex = $templateOccurrenceCount[$betterTemplateKey];
                        
                        $templateLineNumber = $this->getTemplateLineNumber($url, $betterContext, $betterTemplateOccurrenceIndex);
                        if ($templateLineNumber !== null) {
                            $context = $betterContext;
                        }
                    }
                }
            }
            
            $assets[] = [
                'url' => $url,
                'full_tag' => $fullMatch,
                'position' => $position,
                'context' => $context,
                'line_number' => $templateLineNumber ?: $this->getLineNumber($html, $position)
            ];
        }
        
        return $assets;
    }
    
    /**
     * 获取HTML中指定位置的行号
     */
    private function getLineNumber(string $html, int $position): int
    {
        // 回退到基于HTML的行号计算
        return substr_count(substr($html, 0, $position), "\n") + 1;
    }
    
    /**
     * 获取资源在模板文件中的真实行号
     */
    private function getTemplateLineNumber(string $assetUrl, string $templateName, int $occurrenceIndex = 1): ?int
    {
        if (!$assetUrl || !$templateName) {
            return null;
        }
        
        // 获取资源的基础名称
        $assetBaseName = basename($assetUrl, '?' . parse_url($assetUrl, PHP_URL_QUERY));
        
        // 获取模板文件路径
        $templatePath = $this->resolveTemplatePath($templateName);
        if (!$templatePath || !file_exists($templatePath)) {
            return null;
        }
        
        try {
            $templateContent = file_get_contents($templatePath);
            $lines = explode("\n", $templateContent);
            
            // 查找包含该资源的所有行
            $foundLines = [];
            foreach ($lines as $lineNumber => $line) {
                if (strpos($line, $assetBaseName) !== false) {
                    $foundLines[] = $lineNumber + 1; // 行号从1开始
                }
            }
            
            // 根据出现次数返回对应的行号
            if (!empty($foundLines) && isset($foundLines[$occurrenceIndex - 1])) {
                return $foundLines[$occurrenceIndex - 1];
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function findTemplateContext(string $html, int $position, string $requestPath, array $twigTemplates = []): ?string
    {
        // 首先检查资源是否在特定的 Twig block 中
        $blockContext = $this->findBlockContext($html, $position);
        if ($blockContext) {
            // 使用 Profile 数据分析模板继承链来确定资源的真实来源
            $templateWithBlock = $this->findTemplateWithBlockInInheritanceChain($blockContext, $twigTemplates, $requestPath);
            if ($templateWithBlock) {
                // 过滤掉 WebProfiler 相关的模板，这些不应该包含用户资源
                if (!$this->isWebProfilerTemplate($templateWithBlock)) {
                    // 验证模板是否真的可能包含该资源
                    if ($this->validateTemplateContainsAsset($templateWithBlock, $html, $position)) {
                        return $templateWithBlock;
                    }
                }
            }
            return 'block: ' . $blockContext;
        }
        
        // 向前查找最近的 Twig 注释，通常包含模板信息
        $beforeHtml = substr($html, 0, $position);
        
        // 查找 Twig 注释模式，如 {# templates/some/template.html.twig #}
        if (preg_match('/\{#\s*([^#}]+\.html\.twig)\s*#\}(?!.*\{#.*\.html\.twig.*#\})/s', $beforeHtml, $matches)) {
            $templateName = trim($matches[1]);
            if (!$this->isWebProfilerTemplate($templateName)) {
                return $templateName;
            }
        }
        
        // 查找 HTML 注释中的模板信息
        if (preg_match('/<!--\s*([^>]+\.html\.twig)\s*-->(?!.*<!--.*\.html\.twig.*-->)/s', $beforeHtml, $matches)) {
            $templateName = trim($matches[1]);
            if (!$this->isWebProfilerTemplate($templateName)) {
                return $templateName;
            }
        }
        
        // 查找更灵活的 Twig 注释模式，包含部分路径
        if (preg_match('/\{#\s*([^#}]*\.html\.twig[^#}]*)\s*#\}/s', $beforeHtml, $matches)) {
            $templateName = trim($matches[1]);
            if (!$this->isWebProfilerTemplate($templateName)) {
                return $templateName;
            }
        }
        
        // 使用 Profile 数据来确定最可能的模板来源
        if ($this->twigCollector) {
            $profile = $this->twigCollector->getProfile();
            $renderedTemplates = $this->extractRenderedTemplatesFromProfile($profile);
            
            // 过滤掉 WebProfiler 相关的模板
            $userTemplates = array_filter($renderedTemplates, function($template) {
                return !$this->isWebProfilerTemplate($template['name']);
            });
            
            // 根据渲染顺序和深度选择最合适的模板
            if (!empty($userTemplates)) {
                // 根据资源类型选择合适的模板
                $assetUrl = $this->extractAssetUrlFromPosition($html, $position);
                $suitableTemplate = $this->findMostSuitableTemplateForAsset($userTemplates, $assetUrl);
                
                if ($suitableTemplate) {
                    return $suitableTemplate;
                }
                
                // 如果没有找到合适的模板，选择最后渲染的用户模板
                $lastTemplate = end($userTemplates);
                return $lastTemplate['name'];
            }
        }
        
        // 如果有 Twig 模板信息，尝试从中推断
        if (!empty($twigTemplates)) {
            // 过滤掉 WebProfiler 模板
            $userTwigTemplates = array_filter($twigTemplates, function($count, $templateName) {
                return !$this->isWebProfilerTemplate($templateName);
            }, ARRAY_FILTER_USE_BOTH);
            
            if (!empty($userTwigTemplates)) {
                // 获取渲染次数最多的用户模板作为主模板
                $mainTemplate = array_keys($userTwigTemplates, max($userTwigTemplates))[0] ?? null;
                if ($mainTemplate) {
                    return $mainTemplate;
                }
            }
        }
        
        // 根据请求路径推断可能的模板名称，但需要验证模板是否真的包含该资源
        if ($requestPath) {
            // 移除开头的斜杠并转换为模板路径
            $templatePath = ltrim($requestPath, '/');
            if ($templatePath) {
                // 尝试推断模板路径
                $candidateTemplate = null;
                if (strpos($templatePath, 'test/') === 0) {
                    $candidateTemplate = 'templates/' . $templatePath . '.html.twig';
                } elseif (strpos($templatePath, 'admin/') === 0) {
                    $candidateTemplate = 'templates/' . $templatePath . '.html.twig';
                } else {
                    $candidateTemplate = 'templates/' . $templatePath . '.html.twig';
                }
                
                // 验证推断的模板是否真的包含该资源
                if ($candidateTemplate && $this->validateTemplateContainsAsset($candidateTemplate, $html, $position)) {
                    return $candidateTemplate;
                }
            }
        }
        
        return null;
    }
    
    /**
     * 更精确地查找模板上下文，特别针对特定资源
     */
    private function findMorePreciseTemplateContext(string $html, int $position, string $assetUrl): ?string
    {
        $assetBaseName = basename($assetUrl, '?' . parse_url($assetUrl, PHP_URL_QUERY));
        
        // 动态获取渲染的模板列表
        $renderedTemplates = [];
        if ($this->twigCollector) {
            $profile = $this->twigCollector->getProfile();
            $renderedTemplates = $this->extractRenderedTemplatesFromProfile($profile);
        }
        
        // 过滤掉 WebProfiler 相关的模板，获取用户模板列表
        $userTemplates = array_filter($renderedTemplates, function($template) {
            return !$this->isWebProfilerTemplate($template['name']);
        });
        
        // 检查每个模板文件，看哪个真正包含这个资源
        foreach ($userTemplates as $template) {
            $templateName = $template['name'];
            $templatePath = $this->resolveTemplatePath($templateName);
            if ($templatePath && file_exists($templatePath)) {
                try {
                    $templateContent = file_get_contents($templatePath);
                    if (strpos($templateContent, $assetBaseName) !== false) {
                        return $templateName;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        
        return null;
    }
    
    /**
     * 验证模板是否真的可能包含指定的资源
     */
    private function validateTemplateContainsAsset(string $templateName, string $html, int $position): bool
    {
        // 提取资源URL
        $assetUrl = $this->extractAssetUrlFromPosition($html, $position);
        if (!$assetUrl) {
            return false;
        }
        
        // 检查模板文件是否存在并包含该资源
        $templatePath = $this->resolveTemplatePath($templateName);
        if (!$templatePath || !file_exists($templatePath)) {
            return false;
        }
        
        try {
            $templateContent = file_get_contents($templatePath);
            // 检查模板是否包含该资源的引用
            $assetBaseName = basename($assetUrl, '?' . parse_url($assetUrl, PHP_URL_QUERY));
            return strpos($templateContent, $assetBaseName) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 从HTML位置提取资源URL
     */
    private function extractAssetUrlFromPosition(string $html, int $position): ?string
    {
        // 向前和向后查找完整的标签
        $start = max(0, $position - 200);
        $end = min(strlen($html), $position + 200);
        $context = substr($html, $start, $end - $start);
        
        // 查找script或link标签
        if (preg_match('/<(?:script[^>]*src=["\']([^"\'>]+)["\']|link[^>]*href=["\']([^"\'>]+)["\'])[^>]*>/i', $context, $matches)) {
            return $matches[1] ?: $matches[2];
        }
        
        return null;
    }
    
    /**
     * 解析模板路径
     */
    private function resolveTemplatePath(string $templateName): ?string
    {
        // 移除可能的前缀
        $normalizedName = preg_replace('/^templates\//', '', $templateName);
        
        // 构建完整路径
        $basePath = dirname(__DIR__, 2) . '/templates/';
        return $basePath . $normalizedName;
    }
    
    /**
     * 为资源找到最合适的模板
     */
    private function findMostSuitableTemplateForAsset(array $templates, ?string $assetUrl): ?string
    {
        if (!$assetUrl) {
            return null;
        }
        
        $assetBaseName = basename($assetUrl, '?' . parse_url($assetUrl, PHP_URL_QUERY));
        
        // 检查每个模板是否包含该资源
        foreach ($templates as $templateInfo) {
            $templateName = $templateInfo['name'];
            
            // 检查模板文件是否存在并包含该资源
            $templatePath = $this->resolveTemplatePath($templateName);
            if (!$templatePath || !file_exists($templatePath)) {
                continue;
            }
            
            try {
                $templateContent = file_get_contents($templatePath);
                // 检查模板是否包含该资源的引用
                if (strpos($templateContent, $assetBaseName) !== false) {
                    return $templateName;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return null;
    }
    
    /**
     * 检查是否为 WebProfiler 相关的模板
     */
    private function isWebProfilerTemplate(string $templateName): bool
    {
        return strpos($templateName, '@WebProfiler') === 0 || 
               strpos($templateName, 'WebProfiler') !== false ||
               strpos($templateName, 'profiler/') === 0;
    }
    
    /**
     * 从 Profile 中提取所有渲染的模板信息，包括渲染顺序和层次结构
     */
    private function extractRenderedTemplatesFromProfile($profile): array
    {
        $templates = [];
        if (!$profile) {
            return $templates;
        }
        
        $this->collectRenderedTemplatesFromProfile($profile, $templates, 0);
        return $templates;
    }
    
    /**
     * 递归收集渲染的模板信息
     */
    private function collectRenderedTemplatesFromProfile($profile, array &$templates, int $depth): void
    {
        if ($profile->isTemplate()) {
            $templateName = $profile->getTemplate();
            $templates[] = [
                'name' => $templateName,
                'type' => $profile->getType(),
                'depth' => $depth,
                'start_time' => $profile->getStartTime(),
                'duration' => $profile->getDuration(),
            ];
        }
        
        // 递归处理子 Profile
        foreach ($profile as $childProfile) {
            $this->collectRenderedTemplatesFromProfile($childProfile, $templates, $depth + 1);
        }
    }
    
    /**
     * 检查模板是否可能包含指定的 block
     */
    private function templateMightContainBlock(string $templateName, string $blockName, array $templateInfo = []): bool
    {
        $depth = $templateInfo['depth'] ?? $this->estimateTemplateDepth($templateName);
        
        // 根据 block 类型和模板深度判断
        switch ($blockName) {
            case 'javascripts':
            case 'stylesheets':
            case 'head':
            case 'meta':
                // 资源相关的 block 通常在基础模板中定义
                return $depth <= 2;
                
            case 'title':
                // title block 可能在基础模板或布局模板中
                return $depth <= 3;
                
            case 'content':
            case 'main':
            case 'body':
                // 内容相关的 block 可能在任何层级
                return true;
                
            case 'sidebar':
            case 'navigation':
            case 'header':
            case 'footer':
                // 布局相关的 block 通常在布局模板中
                return $depth >= 1 && $depth <= 3;
                
            default:
                // 对于未知的 block，根据模板深度判断
                if ($depth <= 1) {
                    // 基础模板可能包含任何 block
                    return true;
                } elseif ($depth <= 2) {
                    // 布局模板可能包含大部分 block
                    return true;
                } else {
                    // 页面模板主要包含内容相关的 block
                    return !in_array($blockName, ['javascripts', 'stylesheets', 'head', 'meta']);
                }
        }
    }
    
    /**
     * 估算模板的层次深度
     */
    private function estimateTemplateDepth(string $templateName): int
    {
        // 移除 templates/ 前缀
        $normalizedName = preg_replace('/^templates\//', '', $templateName);
        
        // 计算路径深度
        $pathDepth = substr_count($normalizedName, '/');
        
        // 根据模板名称模式调整深度
        if (preg_match('/\b(base|layout)\b/i', $normalizedName)) {
            return max(0, $pathDepth - 1); // 基础和布局模板深度较小
        }
        
        return $pathDepth;
    }
    
    /**
     * 分析模板继承链来确定资源的真实来源
     */
    private function findTemplateWithBlockInInheritanceChain(string $blockName, array $twigTemplates, string $requestPath): ?string
    {
        if (!$this->twigCollector) {
            return null;
        }
        
        // 获取 Profile 对象来动态分析模板渲染链
        $profile = $this->twigCollector->getProfile();
        $renderedTemplates = $this->extractRenderedTemplatesFromProfile($profile);
        
        if (empty($renderedTemplates)) {
            return null;
        }
        
        // 过滤掉 WebProfiler 相关的模板
        $userTemplates = array_filter($renderedTemplates, function($template) {
            return !$this->isWebProfilerTemplate($template['name']);
        });
        
        if (empty($userTemplates)) {
            return null;
        }
        
        // 根据 block 类型采用不同的查找策略
        if (in_array($blockName, ['javascripts', 'stylesheets', 'head', 'body'])) {
            // 对于资源相关的 block，优先查找基础模板（深度较小）
            usort($userTemplates, function($a, $b) {
                return $a['depth'] <=> $b['depth'];
            });
        } else {
            // 对于内容相关的 block，优先查找页面模板（深度较大）
            usort($userTemplates, function($a, $b) {
                return $b['depth'] <=> $a['depth'];
            });
        }
        
        // 查找最可能包含指定 block 的模板
        foreach ($userTemplates as $templateInfo) {
            $templateName = $templateInfo['name'];
            
            // 检查模板是否可能包含该 block
            if ($this->templateMightContainBlock($templateName, $blockName, $templateInfo)) {
                return $templateName;
            }
        }
        
        return null;
    }
    
    private function findBlockContext(string $html, int $position): ?string
    {
        // 查找包含当前位置的 Twig block
        $beforeHtml = substr($html, 0, $position);
        
        // 查找所有 block 开始标签
        if (preg_match_all('/\{%\s*block\s+(\w+)\s*%\}/s', $beforeHtml, $blockMatches, PREG_OFFSET_CAPTURE)) {
            // 从后往前查找最近的 block
            $blockStack = [];
            
            foreach ($blockMatches[1] as $index => $blockMatch) {
                $blockName = $blockMatch[0];
                $blockStartPos = $blockMatches[0][$index][1];
                
                // 检查从这个位置开始是否有对应的 endblock
                $htmlFromBlock = substr($html, $blockStartPos);
                
                // 查找对应的 endblock（考虑嵌套）
                $blockDepth = 1;
                $searchPos = strlen($blockMatches[0][$index][0]);
                
                while ($blockDepth > 0 && $searchPos < strlen($htmlFromBlock)) {
                    // 查找下一个 block 或 endblock
                    if (preg_match('/\{%\s*(block\s+\w+|endblock)\s*%\}/s', $htmlFromBlock, $nextMatch, PREG_OFFSET_CAPTURE, $searchPos)) {
                        $matchText = $nextMatch[1][0];
                        $matchPos = $blockStartPos + $nextMatch[0][1];
                        
                        if (strpos($matchText, 'block ') === 0) {
                            $blockDepth++;
                        } else { // endblock
                            $blockDepth--;
                        }
                        
                        if ($blockDepth === 0) {
                            // 找到了对应的 endblock
                            $endBlockPos = $matchPos + strlen($nextMatch[0][0]);
                            
                            // 如果当前位置在这个 block 范围内
                            if ($position >= $blockStartPos && $position <= $endBlockPos) {
                                $blockStack[] = $blockName;
                            }
                            break;
                        }
                        
                        $searchPos = $nextMatch[0][1] + strlen($nextMatch[0][0]);
                    } else {
                        break;
                    }
                }
            }
            
            // 返回最内层的 block
            return !empty($blockStack) ? end($blockStack) : null;
        }
        
        return null;
    }

    private function findDuplicatesWithContext(array $assets): array
    {
        $urlGroups = [];
        
        // 按 URL 分组
        foreach ($assets as $asset) {
            $url = $asset['url'];
            if (!isset($urlGroups[$url])) {
                $urlGroups[$url] = [];
            }
            $urlGroups[$url][] = $asset;
        }
        
        // 只返回重复的资源
        $duplicates = [];
        foreach ($urlGroups as $url => $group) {
            if (count($group) > 1) {
                // 对重复资源进行更详细的分析
                $analyzedOccurrences = $this->analyzeAssetOccurrences($group);
                
                $duplicates[$url] = [
                    'count' => count($group),
                    'occurrences' => $analyzedOccurrences,
                    'analysis' => $this->generateDuplicationAnalysis($url, $analyzedOccurrences),
                    'is_consecutive' => $this->isConsecutiveDuplication($group),
                    'same_template_duplicates' => $this->findSameTemplateDuplicates($group)
                ];
            }
        }
        
        return $duplicates;
    }
    
    /**
     * 检查是否为连续的重复资源（仅在同一模板中）
     */
    private function isConsecutiveDuplication(array $assetGroup): bool
    {
        if (count($assetGroup) < 2) {
            return false;
        }
        
        // 按模板分组
        $templateGroups = [];
        foreach ($assetGroup as $asset) {
            $template = $asset['context'] ?? 'unknown';
            if (!isset($templateGroups[$template])) {
                $templateGroups[$template] = [];
            }
            $templateGroups[$template][] = $asset;
        }
        
        // 检查每个模板组内是否有连续的行号
        foreach ($templateGroups as $template => $assets) {
            if (count($assets) < 2) {
                continue;
            }
            
            // 按行号排序
            usort($assets, function($a, $b) {
                return ($a['line_number'] ?? 0) - ($b['line_number'] ?? 0);
            });
            
            // 检查是否有连续的行号
            for ($i = 0; $i < count($assets) - 1; $i++) {
                $currentLine = $assets[$i]['line_number'] ?? 0;
                $nextLine = $assets[$i + 1]['line_number'] ?? 0;
                
                // 如果行号相差1或2（考虑空行），认为是连续的
                if ($nextLine - $currentLine <= 2) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * 找出同一模板中的重复资源
     */
    private function findSameTemplateDuplicates(array $assetGroup): array
    {
        $templateGroups = [];
        
        foreach ($assetGroup as $asset) {
            $template = $asset['context'] ?? 'unknown';
            if (!isset($templateGroups[$template])) {
                $templateGroups[$template] = [];
            }
            $templateGroups[$template][] = $asset;
        }
        
        // 只返回有多个资源的模板组
        return array_filter($templateGroups, function($group) {
            return count($group) > 1;
        });
    }
    
    /**
     * 分析资源重复出现的情况
     */
    private function analyzeAssetOccurrences(array $occurrences): array
    {
        $analyzed = [];
        
        foreach ($occurrences as $occurrence) {
            $context = $occurrence['context'];
            
            // 使用动态模板类型判断逻辑
            $sourceType = $this->determineTemplateSourceType($context ?? '');
            $occurrence['source_type'] = $sourceType;
            
            // 根据模板类型设置优先级
            switch ($sourceType) {
                case 'base_template':
                    $occurrence['priority'] = 'high';
                    break;
                case 'layout_template':
                    $occurrence['priority'] = 'medium';
                    break;
                default:
                    $occurrence['priority'] = 'low';
                    break;
            }
            
            $analyzed[] = $occurrence;
        }
        
        // 按优先级排序，高优先级的在前
        usort($analyzed, function($a, $b) {
            $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
            return ($priorityOrder[$b['priority']] ?? 0) - ($priorityOrder[$a['priority']] ?? 0);
        });
        
        return $analyzed;
    }
    
    /**
     * 根据模板上下文和渲染信息动态确定模板类型
     */
    private function determineTemplateSourceType(string $templateContext): string
    {
        if (!$templateContext) {
            return 'page_template';
        }
        
        // 获取渲染的模板信息来辅助判断
        $renderedTemplates = [];
        if ($this->twigCollector) {
            $profile = $this->twigCollector->getProfile();
            $renderedTemplates = $this->extractRenderedTemplatesFromProfile($profile);
        }
        
        // 在渲染的模板中查找当前模板的信息
        $currentTemplateInfo = null;
        foreach ($renderedTemplates as $templateInfo) {
            if ($templateInfo['name'] === $templateContext || 
                str_ends_with($templateInfo['name'], $templateContext)) {
                $currentTemplateInfo = $templateInfo;
                break;
            }
        }
        
        if ($currentTemplateInfo) {
            // 基于渲染深度和模板特征判断模板类型
            return $this->classifyTemplateByDepthAndFeatures(
                $currentTemplateInfo['depth'], 
                $templateContext
            );
        }
        
        // 如果没有找到渲染信息，基于路径和名称模式分析
        return $this->classifyTemplateByPathAnalysis($templateContext);
    }
    
    /**
     * 基于渲染深度和模板特征分类模板
     */
    private function classifyTemplateByDepthAndFeatures(int $depth, string $templateName): string
    {
        // 基于名称模式的强分类
        if (preg_match('/\b(base|app)\b/i', $templateName)) {
            return 'base_template';
        }
        
        if (preg_match('/\b(layout|admin\/layout)\b/i', $templateName)) {
            return 'layout_template';
        }
        
        // 基于渲染深度分类
        if ($depth <= 1) {
            return 'base_template';
        } elseif ($depth <= 2) {
            return 'layout_template';
        } else {
            return 'page_template';
        }
    }
    
    /**
     * 基于路径分析分类模板
     */
    private function classifyTemplateByPathAnalysis(string $templateContext): string
    {
        // 移除可能的前缀
        $normalizedPath = preg_replace('/^(templates\/|block:\s*)/', '', $templateContext);
        
        // 基于名称模式分类
        if (preg_match('/\b(base|app)\.html\.twig$/i', $normalizedPath)) {
            return 'base_template';
        }
        
        if (preg_match('/\b(layout|admin\/layout)\.html\.twig$/i', $normalizedPath) ||
            preg_match('/\/layout\//i', $normalizedPath)) {
            return 'layout_template';
        }
        
        // 基于路径深度分类
        $pathDepth = substr_count($normalizedPath, '/');
        
        if ($pathDepth == 0) {
            return 'base_template';
        } elseif ($pathDepth <= 1) {
            return 'layout_template';
        } else {
            return 'page_template';
        }
    }
    
    /**
     * 生成重复分析报告
     */
    private function generateDuplicationAnalysis(string $url, array $occurrences): string
    {
        $baseTemplateCount = 0;
        $layoutTemplateCount = 0;
        $pageTemplateCount = 0;
        $templateDetails = [];
        $renderingInfo = [];
        
        foreach ($occurrences as $occurrence) {
            $context = $occurrence['context'] ?? '';
            
            switch ($occurrence['source_type']) {
                case 'base_template':
                    $baseTemplateCount++;
                    break;
                case 'layout_template':
                    $layoutTemplateCount++;
                    break;
                case 'page_template':
                    $pageTemplateCount++;
                    break;
            }
            
            // 收集模板详情和渲染信息
            if ($context && !in_array($context, $templateDetails)) {
                $templateDetails[] = $context;
                
                // 尝试获取模板的渲染信息
                $templateRenderInfo = $this->getTemplateRenderingInfo($context);
                if ($templateRenderInfo) {
                    $renderingInfo[] = $templateRenderInfo;
                }
            }
        }
        
        $analysis = [];
        
        // 分析重复类型
        if ($baseTemplateCount > 1) {
            $analysis[] = "基础模板中重复引用 {$baseTemplateCount} 次";
        }
        if ($layoutTemplateCount > 0) {
            $analysis[] = "布局模板中引用 {$layoutTemplateCount} 次";
        }
        if ($pageTemplateCount > 0) {
            $analysis[] = "页面模板中引用 {$pageTemplateCount} 次";
        }
        
        // 分析重复的严重程度
        $totalOccurrences = count($occurrences);
        if ($totalOccurrences >= 3) {
            $analysis[] = "严重重复 ({$totalOccurrences} 次)";
        } elseif ($totalOccurrences == 2) {
            $analysis[] = "轻微重复";
        }
        
        // 添加涉及的具体模板信息
        if (!empty($templateDetails)) {
            $templateList = implode(', ', array_slice($templateDetails, 0, 3));
            if (count($templateDetails) > 3) {
                $templateList .= ' 等';
            }
            $analysis[] = "涉及模板: {$templateList}";
        }
        
        // 添加性能影响分析
        if (!empty($renderingInfo)) {
            $totalRenderTime = array_sum(array_column($renderingInfo, 'duration'));
            if ($totalRenderTime > 0) {
                $analysis[] = sprintf("渲染耗时: %.2fms", $totalRenderTime * 1000);
            }
        }
        
        return implode('; ', $analysis);
    }
    
    /**
     * 获取模板的渲染信息
     */
    private function getTemplateRenderingInfo(string $templateName): ?array
    {
        if (!$this->twigCollector) {
            return null;
        }
        
        try {
            $profile = $this->twigCollector->getProfile();
            $renderedTemplates = $this->extractRenderedTemplatesFromProfile($profile);
            
            foreach ($renderedTemplates as $templateInfo) {
                if ($templateInfo['name'] === $templateName || 
                    str_ends_with($templateInfo['name'], $templateName)) {
                    return [
                        'name' => $templateInfo['name'],
                        'depth' => $templateInfo['depth'],
                        'duration' => $templateInfo['duration'] ?? 0,
                        'start_time' => $templateInfo['start_time'] ?? 0,
                    ];
                }
            }
        } catch (\Exception $e) {
            // 忽略错误
        }
        
        return null;
    }

    public function getCssDuplicates(): array
    {
        return $this->data['duplications']['css'] ?? [];
    }

    public function getJsDuplicates(): array
    {
        return $this->data['duplications']['js'] ?? [];
    }
    
    /**
     * 获取所有重复资源的统计信息
     */
    public function getDuplicationStats(): array
    {
        $cssCount = count($this->getCssDuplicates());
        $jsCount = count($this->getJsDuplicates());
        
        $totalCssOccurrences = 0;
        $totalJsOccurrences = 0;
        
        foreach ($this->getCssDuplicates() as $duplicate) {
            $totalCssOccurrences += $duplicate['count'];
        }
        
        foreach ($this->getJsDuplicates() as $duplicate) {
            $totalJsOccurrences += $duplicate['count'];
        }
        
        return [
            'css_duplicates' => $cssCount,
            'js_duplicates' => $jsCount,
            'total_duplicates' => $cssCount + $jsCount,
            'css_occurrences' => $totalCssOccurrences,
            'js_occurrences' => $totalJsOccurrences,
            'total_occurrences' => $totalCssOccurrences + $totalJsOccurrences,
        ];
    }
    
    /**
     * 获取模板渲染统计信息
     */
    public function getTemplateStats(): array
    {
        if (!$this->twigCollector) {
            return [];
        }
        
        try {
            $profile = $this->twigCollector->getProfile();
            $renderedTemplates = $this->extractRenderedTemplatesFromProfile($profile);
            
            $stats = [
                'total_templates' => count($renderedTemplates),
                'base_templates' => 0,
                'layout_templates' => 0,
                'page_templates' => 0,
                'total_render_time' => 0,
                'templates' => []
            ];
            
            foreach ($renderedTemplates as $template) {
                $templateType = $this->classifyTemplateByDepthAndFeatures(
                    $template['depth'], 
                    $template['name']
                );
                
                switch ($templateType) {
                    case 'base_template':
                        $stats['base_templates']++;
                        break;
                    case 'layout_template':
                        $stats['layout_templates']++;
                        break;
                    case 'page_template':
                        $stats['page_templates']++;
                        break;
                }
                
                $stats['total_render_time'] += $template['duration'] ?? 0;
                $stats['templates'][] = [
                    'name' => $template['name'],
                    'type' => $templateType,
                    'depth' => $template['depth'],
                    'duration' => $template['duration'] ?? 0,
                ];
            }
            
            return $stats;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getName(): string
    {
        return 'app.asset_duplication_collector';
    }
    
    /**
     * 检查是否有重复资源
     */
    public function hasDuplicates(): bool
    {
        $cssDuplicates = $this->getCssDuplicates();
        $jsDuplicates = $this->getJsDuplicates();
        
        // 调试信息：记录到错误日志
        error_log(sprintf(
            '[AssetDuplicationCollector] hasDuplicates check: CSS=%d, JS=%d, Total=%d',
            count($cssDuplicates),
            count($jsDuplicates),
            count($cssDuplicates) + count($jsDuplicates)
        ));
        
        return !empty($cssDuplicates) || !empty($jsDuplicates);
    }
}