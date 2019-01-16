<?php

namespace App\Repository;

use App\Entity\Resident;
use App\Entity\ResidentRent;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentRentRepository
 * @package App\Repository
 */
class ResidentRentRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentRent::class, 'rr')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rr.resident'
            )
            ->groupBy('rr.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('rr');

        return $qb->where($qb->expr()->in('rr.id', $ids))
            ->groupBy('rr.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(array $residentIds)
    {
        $qb = $this->createQueryBuilder('rr');

        return $qb
            ->select('
                    rr.id as id,
                    rr.start as start,
                    rr.amount as amount,
                    r.id as residentId
            ')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'rr.resident = r'
            )
            ->where($qb->expr()->in('r.id', $residentIds))
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) FROM App:ResidentRent mrr JOIN mrr.resident res GROUP BY res.id)')
            ->getQuery()
            ->getResult();
    }
}