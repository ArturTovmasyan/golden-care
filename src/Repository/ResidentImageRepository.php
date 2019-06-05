<?php

namespace App\Repository;

use App\Entity\Resident;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class ResidentImageRepository
 * @package App\Repository
 */
class ResidentImageRepository extends EntityRepository
{
    /**
     * @param $id
     * @return mixed
     */
    public function getBy($id)
    {
        $qb = $this
            ->createQueryBuilder('ri')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = ri.resident'
            )
            ->where('r.id = :id')
            ->setParameter('id', $id);

        return $qb
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $id
     * @param $originalId
     * @return mixed
     */
    public function getFiltersBy($id, $originalId)
    {
        $qb = $this
            ->createQueryBuilder('ri')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = ri.resident'
            )
            ->where('r.id = :id')
            ->andWhere('ri.id != :originalId')
            ->setParameter('id', $id)
            ->setParameter('originalId', $originalId);

        return $qb
            ->getQuery()
            ->getResult();
    }
}