<?php

namespace App\Repository;

use App\Entity\PaymentSource;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PaymentSourceRepository
 * @package App\Repository
 */
class PaymentSourceRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(PaymentSource::class, 'ps')
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = ps.space'
            )
            ->groupBy('ps.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('ps');

        return $qb->where($qb->expr()->in('ps.id', $ids))
            ->groupBy('ps.id')
            ->getQuery()
            ->getResult();
    }
}