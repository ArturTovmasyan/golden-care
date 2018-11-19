<?php

namespace App\Repository;

use App\Entity\CityStateZip;
use App\Entity\Apartment;
use App\Entity\Space;
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
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = a.space'
            )
            ->groupBy('a.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('a');

        return $qb->where($qb->expr()->in('a.id', $ids))
            ->groupBy('a.id')
            ->getQuery()
            ->getResult();
    }
}