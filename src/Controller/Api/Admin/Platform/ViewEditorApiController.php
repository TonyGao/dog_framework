<?php

namespace App\Controller\Api\Admin\Platform;

use App\Controller\Api\ApiResponse;
use App\Entity\Platform\View;
use App\Service\Utils\DomManipulator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ViewEditorApiController extends AbstractController
{
    /**
     * 保存视图
     * 
     * 接收前端传来的视图ID和canvas HTML内容，保存为两个twig文件：
     * 1. 设计器视图文件：包含完整的编辑器画布内容，用于后期编辑
     * 2. 可执行视图文件：去除辅助DOM元素，只保留可执行的视图内容
     */
    #[Route(
        '/api/admin/platform/view/save',
        name: 'api_platform_view_save',
        methods: ['POST']
    )]
    public function saveView(
        Request $request,
        EntityManagerInterface $em,
        DomManipulator $domManipulator
    ): ApiResponse {
        // 获取请求参数
        $payload = $request->toArray();
        dump($payload);
        $viewId = $payload['viewId'] ?? null;
        $canvasHtml = $payload['canvasHtml'] ?? null;
        
        // 参数验证
        if (!$viewId || !$canvasHtml) {
            return ApiResponse::error('视图ID和画布内容不能为空', 400);
        }
        
        try {
            // 查询视图信息
            $view = $em->getRepository(View::class)->find($viewId);
            if (!$view) {
                return ApiResponse::error('视图不存在', 404);
            }
            
            // 获取视图路径
            $viewPath = $view->getPath();
            $viewName = $view->getName();
            
            // 构建文件路径
            $basePath = $this->getParameter('kernel.project_dir') . '/templates/views/';
            $designFilePath = $basePath . $viewPath . '/' . $viewName . '.design.twig';
            $executableFilePath = $basePath . $viewPath . '/' . $viewName . '.html.twig';
            
            // 确保目录存在
            $filesystem = new Filesystem();
            $directory = dirname($designFilePath);
            if (!$filesystem->exists($directory)) {
                $filesystem->mkdir($directory, 0755);
            }
            
            // 保存设计器视图文件
            $filesystem->dumpFile($designFilePath, $canvasHtml);
            
            // 处理可执行视图文件
            $domManipulator->load($canvasHtml);
            
            // 移除设计器辅助DOM元素
            $domManipulator->remove('.add-section-button');
            $domManipulator->remove('.section-header');
            
            // 清理特定的class
            $domManipulator->removeClass('.section.active', 'active');
            $domManipulator->removeClass('.ui-droppable', 'ui-droppable');
            $domManipulator->removeClass('.ef-component-labels', 'ef-component-labels');
            
            // 处理表格单元格的边框样式
            $domManipulator->processTableCells();
            
            // 处理动态字段
            $domManipulator->processDynamicFields();
            
            // 获取处理后的HTML并保存为可执行视图文件
            $executableHtml = $domManipulator->getHtml();
            $filesystem->dumpFile($executableFilePath, $executableHtml);
            
            return ApiResponse::success(json_encode(['message' => '视图保存成功']));
        } catch (\Exception $e) {
            return ApiResponse::error('保存视图失败: ' . $e->getMessage(), 500);
        }
    }
}