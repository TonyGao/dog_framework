# Plan: Enterprise File Upload & Management System

This plan outlines the implementation of a comprehensive file upload and management system for the Symfony framework, supporting local/S3 storage, chunked uploads, image optimization, and admin configuration.

## 1. Dependencies & Configuration
- [ ] Install required packages:
    - `league/flysystem-bundle` (File abstraction)
    - `league/flysystem-aws-s3-v3` (S3 Adapter)
    - `aws/aws-sdk-php` (AWS SDK)
    - `imagine/imagine` (Image processing)
    - `symfony/messenger` (Async tasks)
- [ ] Configure `flysystem.yaml` (basic setup, dynamic adapters will be handled by our Manager).
- [ ] Configure `messenger.yaml` for async image processing.

## 2. Entity Design (Database)
- [ ] Create `App\Entity\Storage\File`:
    - Stores file metadata (UUID, disk, path, original_name, mime, size, hash, width, height, created_at).
- [ ] Create `App\Entity\Storage\StorageConfig`:
    - Stores storage adapter configurations (name, type [local/s3], is_default, config [json], cdn_domain).
- [ ] Create `App\Entity\Storage\UploadSession`:
    - Tracks chunked upload progress (session_id, file_hash, total_chunks, uploaded_chunks [json], status).

## 3. Core Storage Architecture
- [ ] Define `App\Service\Storage\Adapter\StorageAdapterInterface`:
    - Methods: `upload`, `delete`, `exists`, `url`, `temporaryUrl`, `copy`, `move`.
- [ ] Implement `App\Service\Storage\Adapter\LocalStorageAdapter`.
- [ ] Implement `App\Service\Storage\Adapter\S3StorageAdapter`.
- [ ] Create `App\Service\Storage\StorageManager`:
    - Responsible for loading `StorageConfig` from DB and instantiating the correct Adapter.
    - Caching of active adapters.

## 4. File Services
- [ ] Create `App\Service\Storage\ImageOptimizerService`:
    - Uses `Imagine` to resize, compress, and convert to WebP.
    - Handles EXIF orientation.
- [ ] Create `App\Service\Storage\FileUploadService`:
    - Handles the full upload lifecycle: validation -> deduplication check -> storage -> DB record.
    - Handles chunk merging.
- [ ] Create `App\Service\Storage\FileUrlGenerator`:
    - Generates public/private/CDN URLs.
    - Implements caching for URLs.

## 5. Events & Async Processing
- [ ] Define Events:
    - `App\Event\Storage\FileUploadingEvent`
    - `App\Event\Storage\FileUploadedEvent`
    - `App\Event\Storage\FileDeletedEvent`
- [ ] Define Message & Handler:
    - `App\Message\Storage\ImageCompressMessage`
    - `App\MessageHandler\Storage\ImageCompressMessageHandler` (Async optimization).

## 6. API & Controllers
- [ ] Create `App\Controller\Api\FileUploadController`:
    - `POST /api/upload`: Standard upload.
    - `POST /api/upload/chunk`: Chunk upload.
    - `POST /api/upload/complete`: Merge chunks.
    - `GET /api/file/{id}`: Get file info/url.
    - `DELETE /api/file/{id}`: Delete file.
- [ ] Create `App\Controller\Admin\StorageConfigController`:
    - Admin GUI for managing `StorageConfig` entities.
    - Templates: `templates/admin/storage/index.html.twig`, `form.html.twig`.

## 7. Frontend SDK
- [ ] Generate `public/sdk/storage-sdk.js`:
    - JS class with methods: `upload(file)`, `uploadChunked(file)`, `getFile(id)`.

## 8. Verification
- [ ] Verify database schema update.
- [ ] Verify Local upload.
- [ ] Verify S3 upload (mock/dry-run if no credentials).
- [ ] Verify Image optimization.
- [ ] Verify Chunked upload flow.
