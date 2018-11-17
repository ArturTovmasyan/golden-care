<?php

namespace App\Repository;

use App\Entity\CityStateZip;
use App\Entity\Apartment;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ApartmentRepository
 * @package App\Repository
 */
class ApartmentRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Apartment::class, 'a')
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'csz = a.csz'
            )
            ->groupBy('a.id');
    }
}