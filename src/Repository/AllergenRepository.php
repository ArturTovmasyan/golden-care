<?php

namespace App\Repository;

use App\Entity\Allergen;
use App\Entity\Resident;
use App\Entity\ResidentAllergen;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    /**
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(array $residentIds)
    {
        $qb = $this->createQueryBuilder('a');

        return $qb
            ->select('
                    a.id as id,
                    a.title as title,
                    r.id as residentId
            ')
            ->innerJoin(
                ResidentAllergen::class,
                'ra',
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
            ->groupBy('ra.id')
            ->getQuery()
            ->getResult();
    }
}
