<?php

namespace App\Repository\Assessment;

use App\Entity\Assessment\CareLevel;
use App\Entity\Assessment\CareLevelGroup;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CareLevelRepository
 * @package App\Repository\Assessment
 */
class CareLevelRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(CareLevel::class, 'acl')
            ->leftJoin(
                CareLevelGroup::class,
                'aclg',
                Join::WITH,
                'aclg = acl.careLevelGroup'
            )
            ->groupBy('acl.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('acl');

        return $qb->where($qb->expr()->in('acl.id', $ids))
            ->groupBy('acl.id')
            ->getQuery()
            ->getResult();
    }
}