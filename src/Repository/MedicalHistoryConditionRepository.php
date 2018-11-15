<?php

namespace App\Repository;

use App\Entity\MedicalHistoryCondition;
use Doctrine\ORM\EntityRepository;
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
            ->groupBy('mhc.id');
    }
}
