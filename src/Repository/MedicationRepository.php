<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\RelationshipNotFoundException;
use App\Entity\Medication;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class MedicationRepository
 * @package App\Repository
 */
class MedicationRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Medication::class, 'm')
            ->groupBy('m.id');
    }
}