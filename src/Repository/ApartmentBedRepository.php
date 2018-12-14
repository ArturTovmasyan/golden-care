<?php

namespace App\Repository;

use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ApartmentBedRepository
 * @package App\Repository
 */
class ApartmentBedRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ApartmentBed::class, 'ab')
            ->leftJoin(
                ApartmentRoom::class,
                'ar',
                Join::WITH,
                'ar = ab.room'
            )
            ->groupBy('ab.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('ab');

        return $qb->where($qb->expr()->in('ab.id', $ids))
            ->groupBy('ab.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function getBedIdsByRooms($ids)
    {
        $qb = $this->createQueryBuilder('ab');

        return $qb
            ->select(
                'ab.id AS id'
            )
            ->join('ab.room', 'r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}