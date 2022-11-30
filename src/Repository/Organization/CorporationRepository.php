<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Corporation;
use App\Entity\Platform\Entity;
use App\Entity\Platform\EntityPropertyGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CorporationRepository extends ServiceEntityRepository
{
    private $em;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Corporation::class);
        $this->em = $this->getEntityManager();
    }

    /**
     * find group by fqn string
     */
    public function findGroupByFqn($fqn)
    {
        $qb = $this->em->createQueryBuilder();
        return $qb->select('pg.token')
            ->from('App\Entity\Platform\EntityPropertyGroup', 'pg')
            ->where('pg.fqn = :param')
            ->setParameter('param', $fqn)
            ->getQuery()->getResult();
    }

    /**
     * find entity fields by fqn
     */
    public function findFieldsByFqn($fqn)
    {
        $qb = $this->em->createQueryBuilder();
        $result = $qb->select('en')
            ->from('App\Entity\Platform\Entity', 'en')
            ->where('en.fqn = :param')
            ->setParameter('param', $fqn)
            ->getQuery()->getOneOrNullResult()->getProperties();
        return $result;
    }
}
