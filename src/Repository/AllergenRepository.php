<?php

namespace App\Repository;

use App\Entity\Allergen;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class Allergen
 * @package App\Repository
 */
class AllergenRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Allergen::class, 'a')
            ->groupBy('a.id');
    }
}
