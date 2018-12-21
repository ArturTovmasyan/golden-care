<?php

namespace App\Repository;

use App\Entity\MedicalHistoryCondition;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class MedicalHistoryCondition
 * @package App\Repository
 */
class MedicalHistoryConditionRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(MedicalHistoryCondition::class, 'mhc')
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = mhc.space'
            )
            ->groupBy('mhc.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('mhc');

        return $qb->where($qb->expr()->in('mhc.id', $ids))
            ->groupBy('mhc.id')
            ->getQuery()
            ->getResult();
    }
}
