<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\RelationshipNotFoundException;
use App\Entity\Relationship;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class RelationshipRepository
 * @package App\Repository
 */
class RelationshipRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return Paginator
     */
    public function search(QueryBuilder $queryBuilder)
    {
        return new Paginator(
            $queryBuilder
                ->select('r')
                ->from(Relationship::class, 'r')
                ->groupBy('r.id')
                ->getQuery()
        );
    }
}