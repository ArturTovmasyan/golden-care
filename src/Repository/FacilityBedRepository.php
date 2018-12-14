<?php

namespace App\Repository;

use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityBedRepository
 * @package App\Repository
 */
class FacilityBedRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(FacilityBed::class, 'fb')
            ->leftJoin(
                FacilityRoom::class,
                'fr',
                Join::WITH,
                'fr = fb.room'
            )
            ->groupBy('fb.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('fb');

        return $qb->where($qb->expr()->in('fb.id', $ids))
            ->groupBy('fb.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function getBedIdsByRooms($ids)
    {
        $qb = $this->createQueryBuilder('fb');

        return $qb
            ->select(
                'fb.id AS id'
            )
            ->join('fb.room', 'r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}