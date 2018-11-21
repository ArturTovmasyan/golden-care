<?php

namespace App\Repository;

use App\Entity\Diet;
use App\Entity\ResidentDiet;
use App\Entity\Resident;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentDietRepository
 * @package App\Repository
 */
class ResidentDietRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentDiet::class, 'rd')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rd.resident'
            )
            ->leftJoin(
                Diet::class,
                'd',
                Join::WITH,
                'd = rd.diet'
            )
            ->groupBy('rd.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('rd');

        return $qb->where($qb->expr()->in('rd.id', $ids))
            ->groupBy('rd.id')
            ->getQuery()
            ->getResult();
    }
}