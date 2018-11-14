<?php

namespace App\Repository;

use App\Entity\CareLevel;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class CareLevelRepository
 * @package App\Repository
 */
class CareLevelRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return Paginator
     */
    public function searchAll(QueryBuilder $queryBuilder) : Paginator
    {
        return new Paginator(
            $queryBuilder
                ->select('cl')
                ->from(CareLevel::class, 'cl')
                ->getQuery()
        );
    }
}