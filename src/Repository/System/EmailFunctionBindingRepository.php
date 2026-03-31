<?php

namespace App\Repository\System;

use App\Entity\System\EmailFunctionBinding;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailFunctionBinding>
 *
 * @method EmailFunctionBinding|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailFunctionBinding|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailFunctionBinding[]    findAll()
 * @method EmailFunctionBinding[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailFunctionBindingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailFunctionBinding::class);
    }

    public function save(EmailFunctionBinding $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EmailFunctionBinding $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
