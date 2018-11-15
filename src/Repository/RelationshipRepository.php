<?php

namespace App\Repository;

use App\Entity\Relationship;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class RelationshipRepository
 * @package App\Repository
 */
class RelationshipRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Relationship::class, 'r')
            ->groupBy('r.id');
    }
}