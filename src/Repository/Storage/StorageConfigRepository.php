<?php

namespace App\Repository\Storage;

use App\Entity\Storage\StorageConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StorageConfig>
 *
 * @method StorageConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method StorageConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method StorageConfig[]    findAll()
 * @method StorageConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StorageConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StorageConfig::class);
    }

    public function add(StorageConfig $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StorageConfig $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findDefault(): ?StorageConfig
    {
        return $this->findOneBy(['isDefault' => true]);
    }
}
