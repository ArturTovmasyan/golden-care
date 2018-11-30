<?php

namespace App\Repository\Assessment;

use App\Entity\Assessment\Form;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FormRepository
 * @package App\Repository\Assessment
 */
class FormRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Form::class, 'af')
            ->groupBy('af.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('af');

        return $qb->where($qb->expr()->in('af.id', $ids))
            ->groupBy('af.id')
            ->getQuery()
            ->getResult();
    }
}