<?php

namespace App\Repository;

use App\Entity\PaymentSource;
use App\Entity\Space;
use Doctrine\ORM\AbstractQuery;
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

    /**
     * @return mixed
     */
    public function getPaymentSources()
    {
        return $this->createQueryBuilder('ps')
            ->select(
                'ps.id as id',
                'ps.title as title'
            )
            ->orderBy('ps.title', 'ASC')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }
}