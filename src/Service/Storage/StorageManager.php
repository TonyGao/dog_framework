<?php

namespace App\Service\Storage;

use App\Entity\Storage\StorageConfig;
use App\Repository\Storage\StorageConfigRepository;
use App\Service\Storage\Adapter\LocalStorageAdapter;
use App\Service\Storage\Adapter\S3StorageAdapter;
use App\Service\Storage\Adapter\StorageAdapterInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class StorageManager
{
    private array $adapters = [];
    private StorageConfigRepository $configRepo;
    private ParameterBagInterface $params;

    public function __construct(StorageConfigRepository $configRepo, ParameterBagInterface $params)
    {
        $this->configRepo = $configRepo;
        $this->params = $params;
    }

    public function getStorageConfig(?string $disk = null): ?StorageConfig
    {
        if (!$disk) {
            return $this->configRepo->findDefault();
        }
        return $this->configRepo->findOneBy(['name' => $disk]);
    }

    public function getAdapter(?string $disk = null): StorageAdapterInterface
    {
        if (!$disk) {
            $config = $this->configRepo->findDefault();
            if (!$config) {
                // Fallback to local if no default config
                return $this->createLocalAdapter();
            }
            $disk = $config->getName();
        }

        if (isset($this->adapters[$disk])) {
            return $this->adapters[$disk];
        }

        $config = $this->configRepo->findOneBy(['name' => $disk]);
        if (!$config) {
            throw new \InvalidArgumentException("Storage disk '{$disk}' not found.");
        }

        $adapter = $this->createAdapter($config);
        $this->adapters[$disk] = $adapter;

        return $adapter;
    }

    private function createAdapter(StorageConfig $config): StorageAdapterInterface
    {
        switch ($config->getAdapterType()) {
            case 'local':
                $directory = $config->getConfig()['directory'] ?? 'uploads';
                // Trim leading slashes to prevent absolute path confusion
                $directory = ltrim($directory, '/');
                
                $rootPath = $this->params->get('kernel.project_dir') . '/public/' . $directory;
                $publicUrl = '/' . $directory;
                
                return new LocalStorageAdapter($rootPath, $publicUrl);
            
            case 's3':
                return new S3StorageAdapter(array_merge($config->getConfig(), [
                    'cdn_domain' => $config->getCdnDomain()
                ]));

            default:
                throw new \InvalidArgumentException("Unsupported adapter type: " . $config->getAdapterType());
        }
    }

    private function createLocalAdapter(): StorageAdapterInterface
    {
        $rootPath = $this->params->get('kernel.project_dir') . '/public/uploads';
        return new LocalStorageAdapter($rootPath, '/uploads');
    }
}
