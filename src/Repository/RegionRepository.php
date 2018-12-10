<?php

namespace App\Repository;

use App\Entity\Region;
use App\Entity\ResidentRegionOption;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class RegionRepository
 * @package App\Repository
 */
class RegionRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Region::class, 'r')
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->groupBy('r.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('r');

        return $qb->where($qb->expr()->in('r.id', $ids))
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $residentId
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByResident($residentId)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin(
                ResidentRegionOption::class,
                'rro',
                Join::WITH,
                'r = rro.region'
            )
            ->where('rro.resident = :residentId')
            ->setParameter('residentId', $residentId)
            ->groupBy('r.id')
            ->getQuery()
            ->getSingleResult();
    }
}