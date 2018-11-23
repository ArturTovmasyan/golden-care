<?php

namespace App\Repository;

use App\Entity\MedicalHistoryCondition;
use App\Entity\ResidentMedicalHistoryCondition;
use App\Entity\Resident;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentMedicalHistoryConditionRepository
 * @package App\Repository
 */
class ResidentMedicalHistoryConditionRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentMedicalHistoryCondition::class, 'rmhc')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rmhc.resident'
            )
            ->leftJoin(
                MedicalHistoryCondition::class,
                'mhc',
                Join::WITH,
                'mhc = rmhc.condition'
            )
            ->groupBy('rmhc.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('rmhc');

        return $qb->where($qb->expr()->in('rmhc.id', $ids))
            ->groupBy('rmhc.id')
            ->getQuery()
            ->getResult();
    }
}