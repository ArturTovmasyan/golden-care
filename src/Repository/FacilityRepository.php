<?php

namespace App\Repository;

use App\Entity\CityStateZip;
use App\Entity\Facility;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityRepository
 * @package App\Repository
 */
class FacilityRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Facility::class, 'f')
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'csz = f.csz'
            )
            ->groupBy('f.id');
    }
}