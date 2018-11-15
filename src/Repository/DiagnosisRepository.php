<?php

namespace App\Repository;

use App\Entity\Diagnosis;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class Diagnosis
 * @package App\Repository
 */
class DiagnosisRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Diagnosis::class, 'd')
            ->groupBy('d.id');
    }
}
