<?php

namespace App\Repository;

use App\Entity\CityStateZip;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CityStateZipRepository
 * @package App\Repository
 */
class CityStateZipRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(CityStateZip::class, 'csz')
            ->groupBy('csz.id');
    }
}