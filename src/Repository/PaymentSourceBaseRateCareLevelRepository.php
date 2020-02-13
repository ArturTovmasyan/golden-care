<?php

namespace App\Repository;

use App\Entity\PaymentSourceBaseRate;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class PaymentSourceBaseRateCareLevelRepository
 * @package App\Repository
 */
class PaymentSourceBaseRateCareLevelRepository extends EntityRepository
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
                PaymentSourceBaseRate::class,
                'sbr',
                Join::WITH,
                'sbr = brl.baseRate'
            )
            ->where('sbr.id = :baseRateId')
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