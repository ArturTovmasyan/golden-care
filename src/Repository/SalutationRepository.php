<?php

namespace App\Repository;

use App\Entity\Salutation;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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
            ->from(Salutation::class, 'sa')
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = sa.space'
            )
            ->groupBy('sa.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('sa');

        return $qb->where($qb->expr()->in('sa.id', $ids))
            ->groupBy('sa.id')
            ->getQuery()
            ->getResult();
    }
}