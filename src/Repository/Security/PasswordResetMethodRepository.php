<?php

namespace App\Repository\Security;

use App\Entity\Security\PasswordResetMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordResetMethod>
 *
 * @method PasswordResetMethod|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordResetMethod|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordResetMethod[]    findAll()
 * @method PasswordResetMethod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasswordResetMethodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetMethod::class);
    }

    public function add(PasswordResetMethod $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PasswordResetMethod $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return PasswordResetMethod[] Returns an array of PasswordResetMethod objects ordered by priority
     */
    public function findEnabledMethodsOrderedByPriority(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isEnabled = :val')
            ->setParameter('val', true)
            ->orderBy('p.priority', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
