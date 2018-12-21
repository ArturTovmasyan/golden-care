<?php

namespace App\Repository;

use App\Entity\Diet;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DietRepository
 * @package App\Repository
 */
class DietRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Diet::class, 'd')
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = d.space'
            )
            ->groupBy('d.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('d');

        return $qb->where($qb->expr()->in('d.id', $ids))
            ->groupBy('d.id')
            ->getQuery()
            ->getResult();
    }
}