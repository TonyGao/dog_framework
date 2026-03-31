# 企业级 SSE (Server-Sent Events) 与 Mercure 实时通信方案

针对 `enterprise_framework` 的低代码架构，本方案旨在构建一个通用的、框架级的实时通信基础设施。利用 **FrankenPHP** 内置的 **Mercure Hub**，实现高效、稳定的数据同步与即时通讯。

## 1. 核心架构设计

### 1.1 技术栈

- **服务器**: [FrankenPHP](https://frankenphp.dev/) (Worker 模式，`./public/index.php`)
- **协议**: [Mercure](https://mercure.rocks/) (基于 HTTP/2 的 SSE 增强协议，持久化存储使用 BoltDB)
- **浏览器兼容性**: 所有现代浏览器（Chrome, Firefox, Safari, Edge）原生支持 EventSource。IE 不支持，需使用 polyfill 或降级方案。
- **后端**: Symfony Mercure Bundle + `MercureService` (`src/Service/Platform/MercureService.php`)
- **前端**: 原生 `EventSource` + `ef-mercure.js` (`public/lib/ef/base/ef-mercure.js`)

### 1.2 核心理念：Event-Driven UI

不再通过轮询获取数据，而是由后端主动推送"变化事件"。前端监听这些事件，并根据事件类型自动更新 DOM、弹出通知或刷新组件。

---

## 2. 后端实现方案

### 2.1 通用 `MercureService`

位于 `src/Service/Platform/MercureService.php`，是后端推送消息的统一入口，支持多种推送模式：

- **私有推送 (Private)**: 发送给特定用户（如：个人通知）。
- **公共推送 (Public)**: 发送给所有订阅者（如：实体同步消息）。
- **实体同步 (Sync)**: 针对特定实体的变更推送，topic 格式为 `/entity/{entityName}/{id}`。

#### 实际实现接口：

```php
// src/Service/Platform/MercureService.php
namespace App\Service\Platform;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

class MercureService
{
    public function __construct(
        private HubInterface $hub,
        private SerializerInterface $serializer
    ) {}

    /**
     * 推送消息
     * @param string $topic 订阅主题 (如: /user/123/notifications)
     * @param mixed $data 载荷数据（数组或已序列化字符串）
     * @param bool $private 是否为私有消息
     */
    public function publish(string $topic, mixed $data, bool $private = true): string;

    /**
     * 推送实体同步消息
     * Topic 格式：/entity/{entityName}/{id}（全小写）
     * 注意：同步消息为公开推送（private = false）
     */
    public function publishEntitySync(string $entityName, string $id, string $action = 'update', array $changedFields = []): void;

    /**
     * 发送用户通知（私有推送）
     * Topic 格式：/user/{userId}/notifications
     */
    public function notifyUser(string $userId, string $title, string $message, string $level = 'info'): void;
}
```

> **注意**：当前 `MercureService` 尚未实现 `publishBatch()` 方法，如需批量推送，需手动循环调用 `publish()`。

### 2.2 实体自动同步监听器

`EntitySyncListener` 已集成到 Doctrine 生命周期事件中（`src/EventListener/Entity/EntitySyncListener.php`），会自动推送实体变更：

```php
// src/EventListener/Entity/EntitySyncListener.php
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
class EntitySyncListener
{
    public function __construct(private MercureService $mercureService) {}

    private function handleSync(LifecycleEventArgs $args, string $action): void
    {
        $entity = $args->getObject();
        $className = (new \ReflectionClass($entity))->getShortName();

        $id = method_exists($entity, 'getId') ? $entity->getId() : null;
        if ($id) {
            $this->mercureService->publishEntitySync($className, (string)$id, $action);
        }
    }
}
```

> **当前状态**：监听器会对**所有**实体推送同步消息，尚未配置排除名单。如需排除特定实体（如日志、审计表），可通过 `services.yaml` 注入 `$excludedEntities` 参数：

```yaml
# config/services.yaml
App\EventListener\Entity\EntitySyncListener:
    arguments:
        $excludedEntities: ['Log', 'Audit', 'Session', 'CachedEntity']
```

### 2.3 用户在线状态追踪 (Presence)

框架已实现 `PresenceService`（`src/Service/Platform/PresenceService.php`），基于 PSR Cache 接口，60 秒无心跳视为离线：

```php
// src/Service/Platform/PresenceService.php
class PresenceService {
    private const CACHE_PREFIX = 'presence:user:';
    private const ONLINE_TIMEOUT = 60; // 60 秒无心跳视为离线

    public function heartbeat(string $userId): void;   // 更新心跳
    public function isOnline(string $userId): bool;    // 检查是否在线
    public function getLastSeen(string $userId): ?int; // 获取最后活跃时间（Unix 时间戳）
}
```

**API 端点**（`src/Controller/Api/Platform/PresenceApiController.php`，需要 `ROLE_USER`）：

| 端点                        | 方法 | 说明                         |
| --------------------------- | ---- | ---------------------------- |
| `/api/presence/heartbeat`   | POST | 更新当前登录用户的心跳       |
| `/api/presence/check`       | POST | 批量检查用户在线状态         |

> **⚠️ 当前问题**：`/api/presence/check` 的请求体解析尚未实现（`$userIds` 参数未从 Request 中读取），需要补全：

```php
#[Route('/check', name: 'api_presence_check', methods: ['POST'])]
public function check(Request $request): Response
{
    $body = json_decode($request->getContent(), true);
    $userIds = $body['userIds'] ?? [];
    $results = [];
    foreach ($userIds as $userId) {
        $results[$userId] = $this->presenceService->isOnline($userId);
    }
    return ApiResponse::success(json_encode($results));
}
```

---

## 3. 前端实现方案 (`ef-mercure.js`)

文件位于 `public/lib/ef/base/ef-mercure.js`，已在 `base.html.twig` 中全局引入并自动初始化。

### 3.1 已实现的核心功能

1. **自动从 meta 标签读取 Hub URL**: `<meta name="mercure-hub-url" content="...">` （`base.html.twig` 第 25 行已配置）
2. **多主题订阅**: 通过 `subscribe()` 方法动态添加主题（订阅新主题时会重建连接）
3. **DOM 自动同步**: 扫描 `data-ef-sync` 属性并自动订阅对应实体主题
4. **心跳维持**: `startHeartbeat()` 默认每 30 秒 POST 到 `/api/presence/heartbeat`
5. **事件分发**: `on(type, callback)` 按消息类型监听
6. **全局通知**: 收到 `notification` 类型消息时自动调用 `window.ui.alert.show()`

#### Twig 集成（base.html.twig 已处理）

```twig
{# base.html.twig 中已自动初始化，无需在子模板中重复 #}
<meta name="mercure-hub-url" content="{{ mercure_public_url() }}">

{# 初始化脚本（base.html.twig 中已内联） #}
<script>
$(document).ready(function() {
    if (window.EF && window.EF.Mercure) {
        window.EF.Mercure.init();
        window.EF.Mercure.startHeartbeat();
    }
});
</script>
```

### 3.2 前端 API 参考

```javascript
// 基础初始化（base.html.twig 已自动调用，无需在页面中重复）
EF.Mercure.init({
    topics: ['/notifications/global', `/user/${userId}/notifications`],
});

// 监听特定类型消息（推荐方式）
EF.Mercure.on('sync', (data) => {
    console.log(`实体 ${data.entity}:${data.id} 发生了 ${data.action} 操作`);
    // data.fields 包含变更的字段列表
});

EF.Mercure.on('notification', (data) => {
    // 框架已内置处理，会自动调用 window.ui.alert.show()
    // 此处可附加自定义逻辑
});

// 监听所有消息（调试用）
EF.Mercure.on('*', (data) => {
    console.debug('[Mercure]', data);
});

// 动态订阅新主题（订阅后会重建 EventSource 连接）
EF.Mercure.subscribe('/chat/room/general');
EF.Mercure.subscribe(['/topic/a', '/topic/b']); // 支持数组

// 自动 DOM 同步
// 在 HTML 元素上添加 data-ef-sync 属性即可：
// topic 会自动映射为 /entity/{entity}/{id}
// <div data-ef-sync="employee:ae085dad" data-ef-sync-url="/employee/ae085dad/partial">
//     局部内容会自动刷新
// </div>

// 开启用户在线状态心跳（base.html.twig 已自动调用）
EF.Mercure.startHeartbeat(30000);
```

> **⚠️ 注意**：前端 `on('*', callback)` 通配符监听在当前实现中**未生效**。`_handleMessage` 仅按 `data.type` 分发，需要手动扩展才能支持通配符。

### 3.3 TypeScript 类型定义（可选）

```typescript
// ef-mercure.d.ts
declare namespace EF {
    namespace Mercure {
        interface Config {
            hubUrl?: string;
            topics?: string[];
        }

        interface SyncData {
            type: 'sync';
            entity: string;
            id: string;
            action: 'create' | 'update' | 'delete';
            fields?: string[];
            timestamp: number;
        }

        interface NotificationData {
            type: 'notification';
            title: string;
            content: string;
            level?: 'info' | 'success' | 'warning' | 'error';
            icon?: string;
            timestamp: number;
        }

        function init(options?: Config): void;
        function subscribe(topics: string | string[]): void;
        function on(eventType: string, callback: (data: any) => void): void;
        function startHeartbeat(intervalMs?: number): void;
        function disconnect(): void;
    }
}
```

---

## 4. 应用场景细化

### 4.1 单页多 DOM 同步（低代码场景）

- **场景**: 多个 Drawer 开启时，修改了 A Drawer 中的数据，B Drawer 和主页面需要同步。
- **方案**:
  1. 后端 `EntitySyncListener` 在 `postUpdate` 事件中，自动发布 `/entity/{entityName}/{id}` 消息。
  2. 前端 `ef-mercure.js` 监听此类消息，查找页面上所有带有 `data-ef-sync` 属性的元素，执行局部刷新。

**使用示例**：

```html
<!-- 声明式绑定：entity 名用小写，id 为实体 UUID -->
<div
    data-ef-sync="employee:ae085dad"
    data-ef-sync-url="/employee/ae085dad/partial"
>
    <!-- 内容会在收到同步消息时通过 $.load() 自动刷新 -->
</div>
```

### 4.2 后台发送通知

- **场景**: 管理员发布公告，或系统任务处理完成。
- **方案**: 后端调用 `MercureService->notifyUser()`，前端收到 `notification` 类型消息后自动弹出 Alert 通知（`window.ui.alert`）。

```php
// 在 Controller 或 Service 中注入并使用
$this->mercureService->notifyUser(
    $user->getUserIdentifier(),
    '任务完成',
    '您的数据导出已完成，请下载',
    'success'
);
```

### 4.3 用户即时通讯（IM）

- **场景**: 用户 A 发消息给用户 B。
- **方案**:
  1. A 通过 AJAX POST 消息给后端。
  2. 后端存库后，推送到 `/user/{B.id}/notifications`（或自定义 IM 主题）。
  3. B 的前端通过 `on('chat', ...)` 监听到消息后，渲染到聊天列表。

### 4.4 在线状态判断

```php
// 后端判断（使用 PresenceService）
$isOnline = $presenceService->isOnline($user->getUserIdentifier());
$lastSeen = $presenceService->getLastSeen($user->getUserIdentifier());
```

```twig
{# 模板中判断 (需自定义 Twig 扩展) #}
{% if presenceService.isOnline(user.id) %}
    <span class="status-online">在线</span>
{% else %}
    <span class="status-offline">离线</span>
{% endif %}
```

### 4.5 批量通知（当前需手动循环）

当需要向多个用户发送通知时，目前需要循环调用：

```php
// 向所有在线管理员发送通知（publishBatch 尚未实现，手动循环）
$adminIds = $this->entityService->getOnlineAdminIds();
foreach ($adminIds as $adminId) {
    $this->mercureService->notifyUser(
        $adminId,
        '系统公告',
        '系统将于今晚 22:00 进行维护'
    );
}
```

> 后续可在 `MercureService` 中新增 `publishBatch(array $topics, array $data, bool $private = true): array` 方法以提升性能。

---

## 5. 安全与性能优化

### 5.1 JWT 配置（当前配置）

当前 Caddyfile 和 `.env` 的配置如下（开发环境使用 anonymous 模式）：

```caddy
# Caddyfile（当前实际配置）
http://localhost:8000 {
    route /.well-known/mercure {
        mercure {
            transport bolt {
                path /var/mercure.db
            }
            publisher_jwt !ChangeThisMercureHubJWTSecretKey!
            subscriber_jwt !ChangeThisMercureHubJWTSecretKey!
            cors_origins *
            publish_origins *
            anonymous   # 开发环境允许匿名订阅
        }
    }
}
```

```yaml
# config/packages/mercure.yaml（当前实际配置）
mercure:
    hubs:
        default:
            url: '%env(default::MERCURE_URL)%'
            public_url: '%env(MERCURE_PUBLIC_URL)%'
            jwt:
                secret: '%env(MERCURE_JWT_SECRET)%'
                publish: '*'
```

> **⚠️ 生产环境安全提示**：
> - 必须更换默认 JWT Secret（`!ChangeThisMercureHubJWTSecretKey!`）
> - 必须移除 `anonymous` 指令，改为基于 JWT 的订阅权限控制
> - `cors_origins *` 应替换为明确的域名白名单
> - `publish_origins *` 应限制为后端服务 IP

如需生产环境的订阅者 JWT，可实现 `MercureTokenFactory`：

```php
// src/Security/MercureTokenFactory.php（待实现）
class MercureTokenFactory
{
    public function createSubscribeToken(array $topics, array $roles = []): string
    {
        $payload = [
            'mercure' => [
                'subscribe' => $topics,
                'payload'   => ['roles' => $roles]
            ],
            'exp' => time() + 3600
        ];
        return \Firebase\JWT\JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
}
```

### 5.2 FrankenPHP Worker 模式

实际 Worker 入口为 `./public/index.php`（标准 Symfony 入口，非自定义 `frankenphp.php`）：

```
# .env
APP_RUNTIME=Runtime\FrankenPhpSymfony\Runtime
FRANKENPHP_CONFIG="worker ./public/index.php"
```

FrankenPHP 在 Worker 模式下常驻内存运行 PHP，显著提升 SSE 长连接性能，无需修改任何代码。

### 5.3 HTTP/2 与压缩

- FrankenPHP 默认开启 HTTP/2，确保大量 SSE 连接不阻塞浏览器并发限制（HTTP/1.1 的 6 连接上限）。
- Caddyfile 已配置 `encode zstd br gzip`，SSE 流本身不压缩，但其他静态资源受益。
- 前端 `EventSource` 已配置 `{ withCredentials: true }` 以携带 Cookie。

### 5.4 连接管理与内存泄漏防护

当前 `ef-mercure.js` 没有 `beforeunload` 清理逻辑，建议补充：

```javascript
// 页面销毁前务必关闭连接（待补充到 ef-mercure.js）
window.addEventListener('beforeunload', () => {
    EF.Mercure.disconnect();
});
```

同时需补充 `disconnect()` 方法到 `EF_MERCURE` 对象：

```javascript
disconnect: function() {
    if (this.eventSource) {
        this.eventSource.close();
        this.eventSource = null;
    }
    if (this._heartbeatTimer) {
        clearInterval(this._heartbeatTimer);
        this._heartbeatTimer = null;
    }
}
```

### 5.5 连接状态管理

当前库缺少 `getState()` 和 `getTopics()` 等调试方法，建议补充：

```javascript
getState: function() {
    return this.eventSource?.readyState ?? 2; // 2 = CLOSED
},

isConnected: function() {
    return this.getState() === EventSource.OPEN;
},

getTopics: function() {
    return [...this.topics];
}
```

---

## 6. Topic 命名规范与消息协议

### 6.1 Topic 命名规则（当前已实现）

| 类型       | Topic 格式                                  | 示例                                |
| ---------- | ------------------------------------------- | ----------------------------------- |
| 实体同步   | `/entity/{entityName(小写)}/{id}`           | `/entity/employee/ae085dad`         |
| 用户通知   | `/user/{userId}/notifications`              | `/user/user_123/notifications`      |
| 在线状态   | `/ef/presence/user/{id}`（规划中）          | `/ef/presence/user/123`             |
| 即时通讯   | `/ef/chat/user/{id}`（规划中）              | `/ef/chat/user/123`                 |

> **⚠️ 注意**：当前 `MercureService::publishEntitySync()` 生成的 topic 为 `/entity/{name}/{id}`（无 `/ef/` 前缀），前端 `_bindAutoSync()` 也按此规则订阅；方案文档中曾描述的 `/ef/entity/` 前缀**尚未采用**。如需统一，需同步修改后端和前端。

### 6.2 消息格式标准

#### 6.2.1 实体同步消息（Entity Sync）

由 `EntitySyncListener` 自动推送：

```json
{
    "type": "sync",
    "entity": "Employee",
    "id": "ae085dad",
    "action": "update",
    "fields": [],
    "timestamp": 1743232200
}
```

> **注意**：当前 `EntitySyncListener` 未从 Doctrine `changeset` 获取变更字段，`fields` 始终为空数组。如需细粒度字段同步，需在 `postUpdate` 时从 `UnitOfWork` 提取：

```php
public function postUpdate(LifecycleEventArgs $args): void
{
    $entity = $args->getObject();
    $uow = $args->getObjectManager()->getUnitOfWork();
    $changeset = $uow->getEntityChangeSet($entity);
    $changedFields = array_keys($changeset);
    $this->handleSync($args, 'update', $changedFields);
}
```

#### 6.2.2 用户通知消息（Notification）

由 `MercureService->notifyUser()` 推送：

```json
{
    "type": "notification",
    "title": "新订单提醒",
    "content": "您有一笔新订单等待处理",
    "level": "info",
    "icon": "fa-info-circle",
    "timestamp": 1743232200
}
```

#### 6.2.3 聊天消息（Chat，规划中）

```json
{
    "type": "chat",
    "from": "user_123",
    "to": "user_456",
    "content": "消息内容",
    "timestamp": 1743232200
}
```

---

## 7. 开发与调试

### 7.1 前端调试技巧

```javascript
// 查看连接状态（readyState: 0=CONNECTING, 1=OPEN, 2=CLOSED）
console.log('Mercure readyState:', EF.Mercure.eventSource?.readyState);

// 查看所有订阅的主题
console.log('Subscribed topics:', [...EF.Mercure.topics]);

// 查看已注册的监听器
console.log('Listeners:', EF.Mercure.listeners);
```

### 7.2 测试消息推送（开发环境）

开发环境 Caddyfile 已开启 `anonymous` 匿名订阅，可以直接用 curl 测试推送：

```bash
# 推送测试消息（开发环境无需 JWT）
curl -X POST http://localhost:8000/.well-known/mercure \
     -H "Content-Type: application/x-www-form-urlencoded" \
     -d "topic=/entity/employee/test-id&data={\"type\":\"sync\",\"entity\":\"Employee\",\"id\":\"test-id\",\"action\":\"update\",\"fields\":[],\"timestamp\":$(date +%s)}"
```

### 7.3 测试私有订阅

生产环境需生成有效的 Subscriber JWT，开发环境可临时用 `jwtManager`：

```php
// 临时生成测试 Token（仅开发环境使用）
$token = $jwtManager->create([
    'mercure' => ['subscribe' => ['/user/*']]
]);
```

---

## 8. 错误处理与降级机制

### 8.1 前端重连策略

浏览器原生的 `EventSource` 已内置自动重连机制。当前 `ef-mercure.js` 的 `onerror` 仅打印错误日志，如需指数退避控制可扩展：

```javascript
// 增强版重连（可选，扩展到 ef-mercure.js）
class MercureConnectionManager {
    constructor() {
        this.retryDelays = [1000, 2000, 5000, 10000, 30000, 60000];
        this.currentRetry = 0;
        this.maxRetries = 10;
        this.eventSource = null;
    }

    connect(url, options = {}) {
        if (this.eventSource) {
            this.eventSource.close();
        }

        this.eventSource = new EventSource(url, options);

        this.eventSource.onopen = () => {
            this.currentRetry = 0;
            console.log('[Mercure] Connected');
        };

        this.eventSource.onerror = () => {
            if (this.currentRetry >= this.maxRetries) {
                console.error('[Mercure] Max retries reached, stopping');
                this._notifyConnectionLost();
                return;
            }
            const delay = this.retryDelays[Math.min(this.currentRetry, this.retryDelays.length - 1)];
            this.currentRetry++;
            console.warn(`[Mercure] Reconnecting in ${delay}ms (attempt ${this.currentRetry})`);
            setTimeout(() => this.connect(url, options), delay);
        };

        return this.eventSource;
    }

    _notifyConnectionLost() {
        if (navigator.onLine) {
            console.error('[Mercure] Connection lost, please refresh the page');
        } else {
            console.warn('[Mercure] Network offline, waiting for reconnection...');
        }
    }
}
```

### 8.2 授权失效处理

- 收到 401/403 错误时，暂停重连
- 跳转登录页或调用刷新 Token 接口
- 成功后重新建立连接

### 8.3 降级方案

当 SSE 不可用时（企业内网限制、WebSocket 更适合的场景）：

- 自动降级到轮询（`ef-polling.js`，待实现）
- 提供配置项显式指定使用 WebSocket

### 8.4 离线消息队列

- 使用 `localStorage` 暂存未发送的心跳/消息
- 网络恢复后自动重发

---

## 9. 运维与部署

### 9.1 当前 Docker Compose 配置

```yaml
# docker-compose.yml（实际配置）
services:
    frankenphp:
        image: dunglas/frankenphp
        ports:
            - '8080:8080'
        volumes:
            - ./public:/app/public
        environment:
            - MERCURE_JWT_SECRET=${MERCURE_JWT_SECRET}
            - MERCURE_PUBLISHER_JWT_KEY=${MERCURE_PUBLISHER_JWT_KEY}
            - MERCURE_SUBSCRIBER_JWT_KEY=${MERCURE_SUBSCRIBER_JWT_KEY}
            - MERCURE_CORS_ALLOWED_ORIGINS=http://localhost:3000
```

### 9.2 监控指标

推荐使用 Prometheus + Grafana 采集 FrankenPHP 暴露的 metrics。

| 指标                           | 说明           |
| ------------------------------ | -------------- |
| `sse.connections.active`       | 当前活跃连接数 |
| `sse.messages.published.total` | 消息发布总量   |
| `sse.messages.delivered.total` | 消息成功投递量 |
| `sse.reconnect.total`          | 重连次数       |

```yaml
# prometheus.yml
scrape_configs:
    - job_name: 'frankenphp'
      static_configs:
          - targets: ['localhost:8080']
      metrics_path: /metrics
```

### 9.3 高可用部署

当需要部署多个 FrankenPHP 实例时，需要配置独立 Mercure Hub + Redis 适配器：

```yaml
# docker-compose.yml for HA
services:
    mercure:
        image: dunglas/mercure
        ports:
            - '7070:7070'
        environment:
            - MERCURE_JWT_SECRET=${MERCURE_JWT_SECRET}
            - MERCURE_ADDR=:7070
            - MERCURE_PUBLIC_ADDR=https://mercure.example.com
            - MERCURE_SUBSCRIBER_JWT_KEY=${MERCURE_SUBSCRIBER_JWT_KEY}
            - MERCURE_PUBLISHER_JWT_KEY=${MERCURE_PUBLISHER_JWT_KEY}
            - MERCURE_CORS_ALLOWED_ORIGINS=https://your-domain.com
            - MERCURE_EXTERNAL_STORAGE=redis://redis:6379
            - MERCURE_STORAGE_ENABLED=1
        depends_on:
            - redis

    redis:
        image: redis:7-alpine
        volumes:
            - redis_data:/data

volumes:
    redis_data:
```

**故障转移逻辑**：

1. 前端 `EventSource` 本身具备自动重连机制
2. 多实例部署时，Redis 作为消息总线确保发布-订阅一致性
3. 建议前端实现「优雅降级」：SSE 不可用时降级到长轮询

### 9.4 日志规范

- **Mercure Hub**: 记录连接、认证失败消息
- **后端 Service**: 记录消息发布日志（`topic`、`payload size`、`duration`）
- **前端**: 记录连接状态变更、消息接收错误

---

## 10. 冲突处理（多用户同屏编辑）

### 10.1 策略选择

根据业务场景选择合适的策略：

| 策略               | 适用场景             | 实现复杂度 |
| ------------------ | -------------------- | ---------- |
| 最后写入胜出（LWW）| 简单表单、低冲突概率 | 低         |
| 乐观锁             | 需要严格数据一致性   | 中         |
| OT/CRDT            | 多人协作编辑同一字段 | 高         |

### 10.2 简单实现：版本号校验

实体启用 Doctrine 乐观锁：

```php
// src/Entity/YourEntity.php
#[ORM\Entity]
class YourEntity {
    #[ORM\Column(type: 'integer')]
    #[ORM\Version]
    private int $version;
}
```

**带版本检查的更新服务**：

```php
// src/Service/Entity/EntityCRUDService.php
public function updateWithVersionCheck(object $entity, array $data, int $expectedVersion): object
{
    $metadata = $this->entityManager->getClassMetadata(get_class($entity));
    $currentVersion = $metadata->getFieldValue($entity, 'version');

    if ($currentVersion !== $expectedVersion) {
        throw new ConflictException('数据已被其他用户修改，请刷新后重试');
    }

    return $this->save($entity, $data);
}
```

**前端冲突处理 UI**：

```javascript
EF.Mercure.on('sync', (data) => {
    if (data.action === 'update' && data.conflict) {
        window.ui.confirm.show({
            title: '数据冲突',
            content: '该数据已被其他用户修改，请选择保留的版本：',
            onConfirm: () => {
                // 强制使用服务端版本
                EF.Ajax.post(`/api/entity/${data.entity}/${data.id}/force-update`);
            },
            onCancel: () => {
                // 忽略冲突，继续本地编辑
            },
        });
    }
});
```

---

## 11. 测试策略

### 11.1 后端单元测试

```php
// tests/Service/Platform/MercureServiceTest.php
class MercureServiceTest extends KernelTestCase
{
    private MercureService $mercureService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->mercureService = static::getContainer()->get(MercureService::class);
    }

    public function testPublishPrivateMessage(): void
    {
        $messageId = $this->mercureService->publish(
            '/user/123/notifications',
            ['type' => 'notification', 'title' => 'Test', 'content' => 'Hello'],
            true
        );
        $this->assertNotEmpty($messageId);
    }

    public function testPublishEntitySync(): void
    {
        // 需要 Mock HubInterface
        $this->mercureService->publishEntitySync('Employee', 'ae085dad', 'update', ['name']);
        // void 方法，通过 mock 验证是否被调用
    }
}
```

### 11.2 前端单元测试

```javascript
// tests/unit/ef-mercure.test.js
describe('EF.Mercure', () => {
    afterEach(() => {
        EF.Mercure.disconnect();
    });

    it('should subscribe to topics', () => {
        EF.Mercure.subscribe('/test/topic');
        expect(EF.Mercure.topics.has('/test/topic')).toBe(true);
    });

    it('should handle sync messages', (done) => {
        EF.Mercure.on('sync', (data) => {
            expect(data.entity).toBe('Employee');
            expect(data.action).toBe('update');
            done();
        });

        EF.Mercure._handleMessage({
            type: 'sync',
            entity: 'Employee',
            id: '123',
            action: 'update',
        });
    });
});
```

### 11.3 E2E 测试场景

```bash
# 使用 Playwright 进行 E2E 测试（注意：原文档有拼写错误 "mercute"）
npx playwright test --grep "mercure"
```

**测试场景 1：SSE 连接与消息接收**

```javascript
test('SSE connection receives entity update', async ({ page }) => {
    await page.goto('/employee/list');

    const consoleMessages = [];
    page.on('console', (msg) => consoleMessages.push(msg.text()));

    await fetch('/api/entity/Employee/ae085dad', {
        method: 'PUT',
        body: JSON.stringify({ name: 'Updated Name' }),
    });

    await expect(page.locator('#employee-card-ae085dad')).toContainText('Updated Name');
});
```

**测试场景 2：断网重连**

```javascript
test('auto-reconnect after network failure', async ({ page }) => {
    await page.goto('/dashboard');

    await page.context().setOffline(true);
    await page.waitForTimeout(2000);

    await page.context().setOffline(false);

    await expect(page.locator('.connection-status')).toContainText('Connected', {
        timeout: 10000,
    });
});
```

### 11.4 压力测试

**使用 k6 进行 SSE 连接压力测试**：

```javascript
// load-test.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    vus: 500,
    duration: '5m',
};

export default function () {
    const res = http.get(
        'http://localhost:8000/.well-known/mercure?topic=/test',
        { tags: { name: 'SSE_Connection' } }
    );

    check(res, {
        'SSE connection established': (r) => r.status === 200,
    });

    sleep(30); // 保持连接 30 秒
}
```

```bash
# 运行压测
k6 run load-test.js

# 使用 wrk 进行基准测试
wrk -t10 -c500 -d30s http://localhost:8000/.well-known/mercure?topic=/benchmark
```

---

## 12. 待办事项与后续优化

| 优先级 | 项目                                                     | 说明                                               |
| ------ | -------------------------------------------------------- | -------------------------------------------------- |
| 高     | `EntitySyncListener` 补充 `fields` 字段                  | 从 UnitOfWork changeset 提取变更字段               |
| 高     | `PresenceApiController::check()` 补全请求体解析          | 从 `$request->getContent()` 读取 userIds           |
| 高     | `ef-mercure.js` 补充 `disconnect()` 方法                 | 关闭连接并清理心跳定时器                           |
| 中     | 补充 `beforeunload` 清理逻辑                             | 防止页面切换时遗留连接                             |
| 中     | 修复 `on('*', cb)` 通配符监听                            | `_handleMessage` 中补充通配符分发                  |
| 中     | 补充 `getState()` / `getTopics()` 调试方法               | 便于开发调试                                       |
| 中     | 实现 `publishBatch()` 方法                               | 批量推送多主题，提升性能                           |
| 低     | 统一 Topic 前缀（考虑加 `/ef/` 前缀）                    | 需同步修改后端和前端                               |
| 低     | 实现 `MercureTokenFactory`（生产环境 JWT 管理）          | 替换 Caddyfile 中的 `anonymous` 模式               |
| 低     | 实现 `ef-polling.js` 降级轮询方案                        | SSE 不可用时的备选方案                             |
