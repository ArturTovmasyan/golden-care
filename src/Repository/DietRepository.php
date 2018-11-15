<?php

namespace App\Repository;

use App\Entity\Diet;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DietRepository
 * @package App\Repository
 */
class DietRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Diet::class, 'd')
            ->groupBy('d.id');
    }
}