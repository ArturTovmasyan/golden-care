<?php

namespace App\Repository;

use App\Entity\Salutation;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class SalutationRepository
 * @package App\Repository
 */
class SalutationRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return Paginator
     */
    public function searchAll(QueryBuilder $queryBuilder) : Paginator
    {
        return new Paginator(
            $queryBuilder
                ->select('s')
                ->from(Salutation::class, 's')
                ->getQuery()
        );
    }
}