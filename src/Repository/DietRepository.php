<?php

namespace App\Repository;

use App\Entity\Diet;
use App\Entity\Resident;
use App\Entity\ResidentDiet;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = d.space'
            )
            ->groupBy('d.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('d');

        return $qb->where($qb->expr()->in('d.id', $ids))
            ->groupBy('d.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(array $residentIds)
    {
        $qb = $this->createQueryBuilder('d');

        return $qb
            ->select('
                    d.id as id,
                    d.title as title,
                    d.color as color,
                    rd.description as description,
                    r.id as residentId
            ')
            ->innerJoin(
                ResidentDiet::class,
                'rd',
                Join::WITH,
                'rd.diet = d'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'rd.resident = r'
            )
            ->where($qb->expr()->in('r.id', $residentIds))
            ->orderBy('d.title')
            ->groupBy('rd.id')
            ->getQuery()
            ->getResult();
    }
}