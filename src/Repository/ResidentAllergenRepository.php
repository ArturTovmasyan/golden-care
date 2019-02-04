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

    /**
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(array $residentIds)
    {
        $qb = $this->createQueryBuilder('ra');

        return $qb
            ->select('
                    a.id as id,
                    a.title as title,
                    a.description as description,
                    r.id as residentId
            ')
            ->innerJoin(
                Allergen::class,
                'a',
                Join::WITH,
                'ra.allergen = a'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'ra.resident = r'
            )
            ->where($qb->expr()->in('r.id', $residentIds))
            ->orderBy('a.title')
            ->groupBy('ra.id')
            ->getQuery()
            ->getResult();
    }
}