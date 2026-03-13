<?php

namespace App\Service\Storage\Adapter;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

class S3StorageAdapter implements StorageAdapterInterface
{
    private Filesystem $filesystem;
    private S3Client $s3Client;
    private string $bucket;
    private ?string $cdnDomain;

    public function __construct(array $config)
    {
        $s3Config = [
            'credentials' => [
                'key'    => $config['access_key'],
                'secret' => $config['secret_key'],
            ],
            'region' => $config['region'],
            'version' => 'latest',
            'endpoint' => $config['endpoint'] ?? null,
            'use_path_style_endpoint' => $config['path_style'] ?? false,
        ];

        $this->s3Client = new S3Client($s3Config);
        $this->bucket = $config['bucket'];
        $this->cdnDomain = $config['cdn_domain'] ?? null;

        $adapter = new AwsS3V3Adapter($this->s3Client, $this->bucket);
        $this->filesystem = new Filesystem($adapter);
    }

    public function upload(string $path, string $contents, array $config = []): array
    {
        $this->filesystem->write($path, $contents, $config);
        
        return [
            'path' => $path,
            'size' => $this->fileSize($path),
            'mime_type' => $this->mimeType($path),
        ];
    }

    public function delete(string $path): bool
    {
        try {
            $this->filesystem->delete($path);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function exists(string $path): bool
    {
        return $this->filesystem->has($path);
    }

    public function getUrl(string $path): string
    {
        if ($this->cdnDomain) {
            return rtrim($this->cdnDomain, '/') . '/' . ltrim($path, '/');
        }
        
        return $this->s3Client->getObjectUrl($this->bucket, $path);
    }

    public function getTemporaryUrl(string $path, \DateTimeInterface $expiresAt): string
    {
        $cmd = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $path
        ]);

        $request = $this->s3Client->createPresignedRequest($cmd, $expiresAt);

        return (string)$request->getUri();
    }

    public function readStream(string $path)
    {
        return $this->filesystem->readStream($path);
    }

    public function mimeType(string $path): string
    {
        return $this->filesystem->mimeType($path);
    }

    public function fileSize(string $path): int
    {
        return $this->filesystem->fileSize($path);
    }
}
