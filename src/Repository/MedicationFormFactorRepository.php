<?php

namespace App\Repository;

use App\Entity\MedicationFormFactor;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class MedicationFormFactorRepository
 * @package App\Repository
 */
class MedicationFormFactorRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(MedicationFormFactor::class, 'mff')
            ->groupBy('mff.id');
    }
}
