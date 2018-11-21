<?php

namespace App\Repository;

use App\Entity\FacilityRoom;
use App\Entity\Facility;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityRoomRepository
 * @package App\Repository
 */
class FacilityRoomRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(FacilityRoom::class, 'fr')
            ->leftJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = fr.facility'
            )
            ->groupBy('fr.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('fr');

        return $qb->where($qb->expr()->in('fr.id', $ids))
            ->groupBy('fr.id')
            ->getQuery()
            ->getResult();
    }
}