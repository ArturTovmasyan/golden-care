<?php

namespace App\Repository;

use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class SpaceRepository
 * @package App\Repository
 */
class SpaceRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return Paginator
     */
    public function search(QueryBuilder $queryBuilder)
    {
        return new Paginator(
            $queryBuilder
                ->select('s')
                ->from(Space::class, 's')
                ->groupBy('s.id')
                ->getQuery()
        );
    }
}