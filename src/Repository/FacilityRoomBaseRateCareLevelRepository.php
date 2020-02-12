<?php

namespace App\Repository;

use App\Entity\FacilityRoomBaseRate;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class FacilityRoomBaseRateCareLevelRepository
 * @package App\Repository
 */
class FacilityRoomBaseRateCareLevelRepository extends EntityRepository
{
    /**
     * @param $baseRateId
     * @return mixed
     */
    public function getBy($baseRateId)
    {
        $qb = $this
            ->createQueryBuilder('brl')
            ->innerJoin(
                FacilityRoomBaseRate::class,
                'fbr',
                Join::WITH,
                'fbr = brl.baseRate'
            )
            ->where('fbr.id = :baseRateId')
            ->setParameter('baseRateId', $baseRateId);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this
            ->createQueryBuilder('brl')
            ->where('brl.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb->groupBy('brl.id')
            ->getQuery()
            ->getResult();
    }
}