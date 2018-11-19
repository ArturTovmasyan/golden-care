<?php

namespace App\Repository;

use App\Entity\Salutation;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SalutationRepository
 * @package App\Repository
 */
class SalutationRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Salutation::class, 's')
            ->groupBy('s.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('s');

        return $qb->where($qb->expr()->in('s.id', $ids))
            ->groupBy('s.id')
            ->getQuery()
            ->getResult();
    }
}