<?php

namespace App\Repository;

use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SpaceRepository
 * @package App\Repository
 */
class SpaceRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function search(QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->from(Space::class, 's')
            ->groupBy('s.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this
            ->createQueryBuilder('s')
            ->where('s.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->groupBy('s.id')
            ->getQuery()
            ->getResult();
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
            ->createQueryBuilder('s')
            ->select('s.name');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('s.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('s.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('s.id IN (:array)')
                ->setParameter('array', []);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function getLast()
    {
        $qb = $this
            ->createQueryBuilder('s');

        return $qb
            ->orderBy('s.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}