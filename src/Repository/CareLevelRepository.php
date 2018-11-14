<?php

namespace App\Repository;

use App\Entity\CareLevel;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CareLevelRepository
 * @package App\Repository
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
            ->from(CareLevel::class, 'cl')
            ->groupBy('cl.id');
    }
}