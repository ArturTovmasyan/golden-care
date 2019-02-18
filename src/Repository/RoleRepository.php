<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class RoleRepository
 * @package App\Repository
 */
class RoleRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Role::class, 'r')
            ->groupBy('r.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('r');

        $qb->where($qb->expr()->in('r.id', $ids));

        // TODO: add check

        return $qb->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Role
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDefaultRole()
    {
        $qb = $this->createQueryBuilder('r');

        return $qb
            ->where('r.default = :default')
            ->setParameter('default', true)
            ->getQuery()
            ->getOneOrNullResult();
    }
}