<?php

namespace App\Repository;

use App\Entity\Resident;
use App\Entity\ResidentRent;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentRentRepository
 * @package App\Repository
 */
class ResidentRentRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentRent::class, 'rr')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rr.resident'
            )
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = rr.space'
            )
            ->groupBy('rr.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('rr');

        return $qb->where($qb->expr()->in('rr.id', $ids))
            ->groupBy('rr.id')
            ->getQuery()
            ->getResult();
    }
}