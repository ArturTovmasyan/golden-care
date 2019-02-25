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
    public function search(QueryBuilder $queryBuilder) : void
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
}