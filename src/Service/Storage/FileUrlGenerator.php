<?php

namespace App\Service\Storage;

use App\Entity\Storage\File;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class FileUrlGenerator
{
    private StorageManager $storageManager;
    private CacheInterface $cache;
    private int $ttl;

    public function __construct(StorageManager $storageManager, CacheInterface $cache, int $ttl = 3600)
    {
        $this->storageManager = $storageManager;
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function getUrl(File $file): string
    {
        $cacheKey = 'storage_url_' . $file->getId();

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($file) {
            $item->expiresAfter($this->ttl);
            
            $adapter = $this->storageManager->getAdapter($file->getDisk());
            return $adapter->getUrl($file->getPath());
        });
    }

    public function getTemporaryUrl(File $file, \DateTimeInterface $expiresAt): string
    {
        // No caching for temporary/signed URLs as they expire
        $adapter = $this->storageManager->getAdapter($file->getDisk());
        return $adapter->getTemporaryUrl($file->getPath(), $expiresAt);
    }
    
    public function invalidateUrl(File $file): void
    {
        $this->cache->delete('storage_url_' . $file->getId());
    }
}
