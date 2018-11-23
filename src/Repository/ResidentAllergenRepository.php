<?php

namespace App\Repository;

use App\Entity\Allergen;
use App\Entity\ResidentAllergen;
use App\Entity\Resident;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentAllergenRepository
 * @package App\Repository
 */
class ResidentAllergenRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentAllergen::class, 'ra')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = ra.resident'
            )
            ->leftJoin(
                Allergen::class,
                'a',
                Join::WITH,
                'a = ra.allergen'
            )
            ->groupBy('ra.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('ra');

        return $qb->where($qb->expr()->in('ra.id', $ids))
            ->groupBy('ra.id')
            ->getQuery()
            ->getResult();
    }
}