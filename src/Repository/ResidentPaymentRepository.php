<?php

namespace App\Repository;

use App\Entity\Resident;
use App\Entity\ResidentPayment;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentPaymentRepository
 * @package App\Repository
 */
class ResidentPaymentRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentPayment::class, 'rp')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rp.resident'
            )
            ->groupBy('rp.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('rp');

        return $qb->where($qb->expr()->in('rp.id', $ids))
            ->groupBy('rp.id')
            ->getQuery()
            ->getResult();
    }
}