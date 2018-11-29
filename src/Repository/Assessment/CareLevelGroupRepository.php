<?php

namespace App\Repository\Assessment;

use App\Entity\Assessment\CareLevelGroup;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CareLevelGroupRepository
 * @package App\Repository\Assessment
 */
class CareLevelGroupRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(CareLevelGroup::class, 'aclg')
            ->groupBy('aclg.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('aclg');

        return $qb->where($qb->expr()->in('aclg.id', $ids))
            ->groupBy('aclg.id')
            ->getQuery()
            ->getResult();
    }
}