# 通用文件上传与存储系统文档

本文档详细介绍了企业级框架中通用文件上传与存储系统的功能、使用方法及底层实现原理。该系统旨在提供统一、高性能、可扩展的文件存储解决方案，支持本地存储及 AWS S3 兼容的云存储服务。

---

## 第一部分：使用手册 (User Manual)

### 1. 系统简介

本系统提供了一套完整的“上传-存储-管理-分发”解决方案，主要特性包括：

- **多驱动支持**：无缝切换本地磁盘与云对象存储（如 AWS S3, Aliyun OSS, MinIO 等）。
- **可视化配置**：通过后台管理界面动态配置存储策略，无需修改代码。
- **高性能上传**：支持大文件分块上传、断点续传及秒传（基于文件 Hash 去重）。
- **自动化处理**：图片上传后自动进行压缩、尺寸调整及 WebP 转换（异步队列处理）。
- **开发者友好**：提供开箱即用的 JavaScript SDK 和统一的后端 API。

### 2. 配置指南

系统管理员可通过后台管理界面配置存储后端。

#### 2.1 访问配置界面

进入后台管理系统，导航至 **系统设置 > 存储配置** (`/admin/storage`)。

#### 2.2 添加存储配置

点击“新建配置”，填写以下信息：

- **基本信息**：
  - **存储名称**：唯一标识符（如 `local_public`, `s3_backup`）。
  - **驱动类型**：选择 `Local` (本地) 或 `S3 Compatible` (对象存储)。
  - **设为默认**：勾选后，未指定磁盘的上传请求将自动使用此配置。
  - **CDN 域名**：(可选) 配置后，生成的文件 URL 将使用此域名（如 `https://cdn.example.com`）。

- **Local (本地存储) 配置参数**：
  - **Root Path**：文件存储的服务器绝对路径（默认：`%kernel.project_dir%/public/uploads`）。
  - **Public URL**：外部访问的基础 URL 路径（默认：`/uploads`）。

- **S3 (对象存储) 配置参数**：
  - **Endpoint**：对象存储服务的 API 地址（如 `https://oss-cn-hangzhou.aliyuncs.com`）。
  - **Region**：区域代码（如 `cn-hangzhou`）。
  - **Bucket**：存储桶名称。
  - **Access Key** / **Secret Key**：访问凭证。

### 3. 前端开发指南

系统内置了标准 JavaScript SDK，简化了文件上传的对接工作。

#### 3.1 引入 SDK

在 HTML 页面中引入 SDK 文件：

```html
<script src="/sdk/storage-sdk.js"></script>
```

#### 3.2 初始化

```javascript
const storage = new StorageSDK({
  baseUrl: '/api/storage', // API 基础路径
  chunkSize: 2 * 1024 * 1024, // 分块大小 (默认 2MB)
});
```

#### 3.3 普通文件上传

适用于小文件（建议 < 20MB）。

```javascript
const fileInput = document.getElementById('file');

storage
  .upload(fileInput.files[0], {
    disk: 's3_backup', // (可选) 指定存储磁盘
    optimize: true, // (可选) 是否开启图片自动优化
  })
  .then((response) => {
    console.log('上传成功:', response);
    // { id: "uuid...", url: "https://...", ... }
  })
  .catch((error) => {
    console.error('上传失败:', error);
  });
```

#### 3.4 大文件分块上传

适用于大文件，支持进度监控和断点续传。

```javascript
storage
  .uploadChunked(fileInput.files[0], {
    disk: 'default',
    onProgress: (percentage) => {
      console.log(`上传进度: ${(percentage * 100).toFixed(2)}%`);
    },
  })
  .then((response) => {
    console.log('分块上传完成:', response);
  });
```

### 4. 后端开发指南

#### 4.1 服务调用

在 Controller 或 Service 中注入 `FileUploadService` 进行文件处理。

```php
use App\Service\Storage\FileUploadService;

class MyController extends AbstractController
{
    public function uploadAction(Request $request, FileUploadService $uploadService)
    {
        $file = $request->files->get('avatar');

        // 上传文件
        $fileEntity = $uploadService->upload($file, [
            'disk' => 's3_prod',
            'optimize' => true
        ]);

        return $this->json(['url' => $fileEntity->getPath()]);
    }
}
```

#### 4.2 事件监听

系统会在上传生命周期的不同阶段分发事件，开发者可监听这些事件以扩展业务逻辑（如生成水印、OCR 识别）。

- `App\Event\Storage\FileUploadingEvent`: 上传前触发，可用于修改上传选项或校验。
- `App\Event\Storage\FileUploadedEvent`: 上传完成并持久化后触发。
- `App\Event\Storage\FileDeletedEvent`: 文件删除后触发。

---

## 第二部分：实现原理 (Implementation Principles)

### 1. 架构设计

系统采用分层架构设计，遵循 **SOLID** 原则，确保核心逻辑与具体存储实现解耦。

#### 1.1 核心组件

- **StorageManager (Factory)**: 负责根据配置读取数据库中的 `StorageConfig`，并实例化对应的 `StorageAdapter`。实现了享元模式，缓存已实例化的适配器。
- **StorageAdapterInterface (Interface)**: 定义了统一的存储操作契约 (`upload`, `delete`, `exists`, `getUrl` 等)。
- **Adapters (Implementation)**:
  - `LocalStorageAdapter`: 基于 `league/flysystem-local`，操作本地文件系统。
  - **S3StorageAdapter**: 基于 `aws-sdk-php` 和 `flysystem-aws-s3-v3`，实现了 S3 协议的通用适配。

#### 1.2 数据模型 (Entity)

- **File**: 核心元数据表。记录文件的 UUID、物理路径、原始文件名、MIME 类型、大小、Hash 值（用于去重）、宽高等信息。
- **StorageConfig**: 存储配置表。将连接参数（Endpoint, Key 等）序列化存储，支持动态扩展。
- **UploadSession**: 分块上传会话表。记录大文件上传的状态、已上传分块索引，用于断点续传和合并验证。

### 2. 核心流程机制

#### 2.1 文件去重与秒传

为了节省存储空间和提升用户体验，系统实现了基于内容的去重机制。

1. **Hash 计算**: 上传前计算文件的 `SHA-256` 哈希值。
2. **查重**: 在 `files` 数据库表中查找是否存在相同 Hash 的记录。
3. **秒传**: 如果存在，直接返回已有文件的元数据和 URL，无需再次执行物理上传。

#### 2.2 大文件分块上传流程

1. **初始化**: 前端将文件切片，SDK 自动生成 `session_id`。
2. **分块传输**: 逐个 POST 分块数据到 `/api/storage/upload/chunk`。后端将分块暂存于系统临时目录 (`sys_get_temp_dir`)，并更新 `UploadSession` 记录。
3. **合并**: 所有分块上传完成后，前端调用 `/api/storage/upload/complete`。
4. **组装与存储**: 后端校验分块完整性，合并文件流，模拟 `UploadedFile` 对象调用核心上传逻辑，最后清理临时文件和会话记录。

#### 2.3 异步图片处理

为了不阻塞 HTTP 请求，图片优化采用异步消息队列处理。

1. **触发**: 图片上传完成后，`FileUploadService` 发送 `ImageCompressMessage` 到 Symfony Messenger 总线。
2. **消费**: 后台 Worker 进程捕获消息，调用 `ImageOptimizerService`。
3. **处理**: 使用 `Imagine` 库加载原图，执行压缩、EXIF 旋转修正、WebP 转换等操作，并覆盖或另存优化后的资源。

### 3. 性能与安全

#### 3.1 缓存策略 (Redis)

- **URL 缓存**: `FileUrlGenerator` 使用 Redis 缓存生成的文件 URL（TTL 可配置），避免频繁调用 S3 `getObjectUrl` 或数据库查询。
- **配置缓存**: 存储驱动配置在初次加载后会被缓存，减少数据库读取。

#### 3.2 安全机制

- **文件名随机化**: 所有上传文件强制重命名为 UUID 格式，物理路径按 `YYYY/MM/DD/UUID.ext` 散列存储，防止文件名冲突和遍历攻击。
- **MIME 校验**: 严格检查文件 MIME 类型，结合扩展名白名单，防止恶意脚本上传。
- **访问控制**:
  - **Public**: 公开文件通过 CDN 或 Web Server 直接访问。
  - **Private**: 私有文件通过 `StorageAdapter::getTemporaryUrl()` 生成带签名的临时访问链接（S3 Presigned URL）。

### 4. 代码组织结构

本系统严格按照 Symfony 最佳实践组织代码：

- `src/Controller/Api/FileUploadController.php`: 统一上传 API 接口。
- `src/Controller/Admin/StorageConfigController.php`: 后台配置管理控制器。
- `src/Service/Storage/`: 核心服务层。
  - `Adapter/`: 存储适配器 (`LocalStorageAdapter`, `S3StorageAdapter`)。
  - `FileUploadService.php`: 上传业务逻辑门面。
  - `StorageManager.php`: 适配器工厂与管理器。
  - `ImageOptimizerService.php`: 图片处理服务。
  - `FileUrlGenerator.php`: URL 生成与缓存服务。
- `src/Entity/Storage/`: 数据实体 (`File`, `StorageConfig`, `UploadSession`)。
- `src/Repository/Storage/`: 数据仓储。
- `src/Event/Storage/`: 事件类定义。
- `src/Message/Storage/`: 异步消息定义。
- `src/MessageHandler/Storage/`: 消息处理器。
- `public/sdk/storage-sdk.js`: 前端 JavaScript SDK。

### 5. 系统依赖

- **PHP 8.2+**
- **Symfony 6/7**
- **Flysystem v3**: 文件系统抽象层。
- **AWS SDK for PHP**: S3 协议支持。
- **Imagine / Intervention Image**: 图片处理库。
- **Redis**: 缓存与会话存储。
- **Doctrine ORM**: 数据库操作。
- **Symfony Messenger**: 异步任务处理。
