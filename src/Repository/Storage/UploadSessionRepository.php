<?php

namespace App\Repository\Storage;

use App\Entity\Storage\UploadSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UploadSession>
 *
 * @method UploadSession|null find($id, $lockMode = null, $lockVersion = null)
 * @method UploadSession|null findOneBy(array $criteria, array $orderBy = null)
 * @method UploadSession[]    findAll()
 * @method UploadSession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UploadSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UploadSession::class);
    }

    public function add(UploadSession $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UploadSession $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function cleanupExpired(): int
    {
        return $this->createQueryBuilder('s')
            ->delete()
            ->where('s.expiresAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}
