<?php

namespace App\Repository;

use App\Entity\ResidentHealthInsurance;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class ResidentHealthInsuranceFileRepository
 * @package App\Repository
 */
class ResidentHealthInsuranceFileRepository extends EntityRepository
{
    /**
     * @param $id
     * @return mixed
     */
    public function getBy($id)
    {
        $qb = $this
            ->createQueryBuilder('rhif')
            ->innerJoin(
                ResidentHealthInsurance::class,
                'rhi',
                Join::WITH,
                'rhi = rhif.insurance'
            )
            ->where('rhi.id = :id')
            ->setParameter('id', $id);

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this
            ->createQueryBuilder('rhif')
            ->innerJoin(
                ResidentHealthInsurance::class,
                'rhi',
                Join::WITH,
                'rhi = rhif.insurance'
            )
            ->where('rhi.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->getQuery()
            ->getResult();
    }
}