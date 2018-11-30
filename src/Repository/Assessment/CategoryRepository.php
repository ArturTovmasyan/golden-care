<?php

namespace App\Repository\Assessment;

use App\Entity\Assessment\Category;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CategoryRepository
 * @package App\Repository
 */
class CategoryRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Category::class, 'ac')
            ->groupBy('ac.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('ac');

        return $qb->where($qb->expr()->in('ac.id', $ids))
            ->groupBy('ac.id')
            ->getQuery()
            ->getResult();
    }
}