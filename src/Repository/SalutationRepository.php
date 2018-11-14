<?php

namespace App\Repository;

use App\Entity\Salutation;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SalutationRepository
 * @package App\Repository
 */
class SalutationRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Salutation::class, 's')
            ->groupBy('s.id');
    }
}