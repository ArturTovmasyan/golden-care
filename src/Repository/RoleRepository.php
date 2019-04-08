<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Role;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class RoleRepository
 * @package App\Repository
 */
class RoleRepository extends EntityRepository implements RelatedInfoInterface
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
        $qb = $this
            ->createQueryBuilder('r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids);

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

    /**
     * @param array $criteria
     * @return array
     */
    public function getRoleByDefaultCriteria(array $criteria)
    {
        if (1 == $criteria['default']) {
            return $this->createQueryBuilder('r')
                ->andWhere('r.default = :default')
                ->setParameter('default', $criteria['default'])
                ->getQuery()
                ->getResult()
                ;
        }

        return [];
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param null $mappedBy
     * @param null $id
     * @param array|null $ids
     * @return mixed
     */
    public function getRelatedData(Space $space = null, array $entityGrants = null, $mappedBy = null, $id = null, array $ids = null)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->select('r.name');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('r.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('r.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('r.id IN (:array)')
                ->setParameter('array', []);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}