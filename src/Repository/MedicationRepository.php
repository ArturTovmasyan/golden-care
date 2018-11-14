<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\RelationshipNotFoundException;
use App\Entity\Medication;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class MedicationRepository
 * @package App\Repository
 */
class MedicationRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return Paginator
     */
    public function search(QueryBuilder $queryBuilder)
    {
        return new Paginator(
            $queryBuilder
                ->select('m')
                ->from(Medication::class, 'm')
                ->groupBy('m.id')
                ->getQuery()
        );
    }
}