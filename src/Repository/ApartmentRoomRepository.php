<?php

namespace App\Repository;

use App\Entity\ApartmentRoom;
use App\Entity\Apartment;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ApartmentRoomRepository
 * @package App\Repository
 */
class ApartmentRoomRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ApartmentRoom::class, 'ar')
            ->leftJoin(
                Apartment::class,
                'a',
                Join::WITH,
                'a = ar.apartment'
            )
            ->groupBy('ar.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('ar');

        return $qb->where($qb->expr()->in('ar.id', $ids))
            ->groupBy('ar.id')
            ->getQuery()
            ->getResult();
    }
}