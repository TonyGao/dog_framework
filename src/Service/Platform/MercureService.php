<?php

namespace App\Service\Platform;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * 框架通用 Mercure 推送服务
 * 用于全站实时的消息推送、DOM 同步和通知
 */
class MercureService
{
    private HubInterface $hub;
    private SerializerInterface $serializer;

    public function __construct(HubInterface $hub, SerializerInterface $serializer)
    {
        $this->hub = $hub;
        $this->serializer = $serializer;
    }

    /**
     * 推送一个通用更新
     * 
     * @param string $topic 订阅的主题，例如：/user/123/notifications
     * @param mixed $data 数据对象或数组，将被序列化为 JSON
     * @param bool $private 是否为私有推送（需要 JWT 授权订阅）
     */
    public function publish(string $topic, mixed $data, bool $private = true): string
    {
        $jsonContent = is_string($data) ? $data : $this->serializer->serialize($data, 'json');

        $update = new Update(
            $topic,
            $jsonContent,
            $private
        );

        return $this->hub->publish($update);
    }

    /**
     * 推送实体变更通知，用于多页面/DOM 同步
     * 
     * @param string $entityName 实体名称 (如: Employee)
     * @param string $id 实体 ID
     * @param string $action 操作类型 (update, create, delete)
     * @param array $changedFields 变更的字段列表
     */
    public function publishEntitySync(string $entityName, string $id, string $action = 'update', array $changedFields = []): void
    {
        $topic = sprintf('/entity/%s/%s', strtolower($entityName), $id);
        
        $this->publish($topic, [
            'type' => 'sync',
            'entity' => $entityName,
            'id' => $id,
            'action' => $action,
            'fields' => $changedFields,
            'timestamp' => time()
        ], false); // 同步消息通常是公开的或由前端控制权限
    }

    /**
     * 发送系统通知给特定用户
     * 
     * @param string $userId 用户 ID
     * @param string $title 标题
     * @param string $message 内容
     * @param string $level 级别 (info, success, warning, error)
     */
    public function notifyUser(string $userId, string $title, string $message, string $level = 'info'): void
    {
        $topic = sprintf('/user/%s/notifications', $userId);
        
        $this->publish($topic, [
            'type' => 'notification',
            'title' => $title,
            'content' => $message,
            'level' => $level,
            'icon' => $this->getIconByLevel($level),
            'timestamp' => time()
        ]);
    }

    private function getIconByLevel(string $level): string
    {
        return match ($level) {
            'success' => 'fa-check-circle',
            'warning' => 'fa-exclamation-triangle',
            'error' => 'fa-times-circle',
            default => 'fa-info-circle',
        };
    }
}
