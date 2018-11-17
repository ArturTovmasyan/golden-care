<?php

namespace App\Repository;

use App\Entity\Region;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class RegionRepository
 * @package App\Repository
 */
class RegionRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Region::class, 'r')
            ->groupBy('r.id');
    }
}