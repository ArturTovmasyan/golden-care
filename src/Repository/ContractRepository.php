<?php

namespace App\Repository;

use App\Entity\Resident;
use App\Entity\Contract;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ContractRepository
 * @package App\Repository
 */
class ContractRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Contract::class, 'c')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = c.resident'
            )
            ->groupBy('c.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('c');

        return $qb->where($qb->expr()->in('c.id', $ids))
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }
}