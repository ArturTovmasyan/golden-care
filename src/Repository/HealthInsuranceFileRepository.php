<?php

namespace App\Repository;

use App\Entity\HealthInsurance;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class HealthInsuranceFileRepository
 * @package App\Repository
 */
class HealthInsuranceFileRepository extends EntityRepository
{
    /**
     * @param $id
     * @return mixed
     */
    public function getBy($id)
    {
        $qb = $this
            ->createQueryBuilder('hif')
            ->innerJoin(
                HealthInsurance::class,
                'hi',
                Join::WITH,
                'hi = hif.insurance'
            )
            ->where('hi.id = :id')
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
            ->createQueryBuilder('hif')
            ->innerJoin(
                HealthInsurance::class,
                'hi',
                Join::WITH,
                'hi = hif.insurance'
            )
            ->where('hi.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->getQuery()
            ->getResult();
    }
}