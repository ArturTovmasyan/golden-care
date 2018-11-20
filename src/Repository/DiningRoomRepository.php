<?php

namespace App\Repository;

use App\Entity\DiningRoom;
use App\Entity\Facility;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DiningRoomRepository
 * @package App\Repository
 */
class DiningRoomRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(DiningRoom::class, 'dr')
            ->leftJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = dr.facility'
            )
            ->groupBy('dr.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('dr');

        return $qb->where($qb->expr()->in('dr.id', $ids))
            ->groupBy('dr.id')
            ->getQuery()
            ->getResult();
    }
}