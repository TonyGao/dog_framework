<?php

namespace App\Service\Platform;

use App\Entity\Platform\DatabaseConnection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;

class DatabaseConnectionManager
{
  public function __construct(
    private EntityManagerInterface $em,
    private CacheItemPoolInterface $cache,
  ) {}

  public function getConnectionById(string $id): Connection
  {
    // 缓存连接参数
    $cacheKey = 'db_conn_params_' . $id;
    $cacheItem = $this->cache->getItem($cacheKey);
    
    if ($cacheItem->isHit()) {
      // 缓存命中，直接使用缓存的参数
      $params = $cacheItem->get();
    } else {
      // 缓存未命中，从数据库查询并缓存
      $dbConfig = $this->em->getRepository(DatabaseConnection::class)->find($id);

      if (!$dbConfig) {
        throw new \RuntimeException("Database connection not found: $id");
      }

      $params = $this->buildParams($dbConfig);
      $cacheItem->set($params);
      $cacheItem->expiresAfter(3600);
      $this->cache->save($cacheItem);
    }

    return DriverManager::getConnection($params);
  }

  private function buildParams(DatabaseConnection $dbConfig): array
  {
    if ($dbConfig->getConnectionMode() === 'dsn') {
      return [
        'url' => $this->decryptIfNeeded($dbConfig->getRawDsn())
      ];
    }

    return [
      'driver' => $dbConfig->getDriver(),
      'host' => $dbConfig->getHost(),
      'port' => $dbConfig->getPort(),
      'dbname' => $dbConfig->getDatabase(),
      'user' => $dbConfig->getUsername(),
      'password' => $dbConfig->getPassword(),
    ];
  }

  private function decryptIfNeeded(?string $value): ?string
  {
    if (!$value) return $value;

    // 这里可以用 Symfony Secrets / openssl 解密
    return $value; // 假装已经解密
  }
}
