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
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this
            ->createQueryBuilder('ri')
            ->select('
                r.id as id, 
                ri.photo_150_150 as photo_150_150
            ')
            ->join(
                Resident::class,
                'r',
                Join::WITH,
                'r = ri.resident'
            )
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->getQuery()
            ->getResult();
    }
}