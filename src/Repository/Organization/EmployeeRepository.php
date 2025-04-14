<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method Employee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employee[]    findAll()
 * @method Employee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeeRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    /**
     * 用于自动升级密码的哈希算法
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Employee) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * 查找在职员工
     */
    public function findActiveEmployees()
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.status = :status')
            ->setParameter('status', 'active')
            ->orderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据部门查找员工
     */
    public function findByDepartment($departmentId)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.department = :departmentId')
            ->setParameter('departmentId', $departmentId)
            ->orderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据岗位查找员工
     */
    public function findByPosition($positionId)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.position = :positionId')
            ->setParameter('positionId', $positionId)
            ->orderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找某个经理的所有下属
     */
    public function findSubordinates($managerId)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.manager = :managerId')
            ->setParameter('managerId', $managerId)
            ->orderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}