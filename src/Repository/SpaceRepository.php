<?php

namespace App\Repository;

use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SpaceRepository
 * @package App\Repository
 */
class SpaceRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Space::class, 's')
            ->groupBy('s.id');
    }
}