<?php

namespace App\Repository;

use App\Entity\CityStateZip;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class CityStateZipRepository
 * @package App\Repository
 */
class CityStateZipRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return Paginator
     */
    public function searchAll(QueryBuilder $queryBuilder) : Paginator
    {
        return new Paginator(
            $queryBuilder
                ->select('csz')
                ->from(CityStateZip::class, 'csz')
                ->getQuery()
        );
    }
}