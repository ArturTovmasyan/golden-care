<?php

namespace App\Repository;

use App\Entity\Resident;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class ImageRepository
 * @package App\Repository
 */
class ImageRepository extends EntityRepository
{
    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this
            ->createQueryBuilder('i')
            ->where('i.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByResidentIds($ids)
    {
        $qb = $this
            ->createQueryBuilder('i')
            ->select('
                r.id as id, 
                i.type as type,
                i.s3Id_150_150 as s3Id,
                i.s3Uri_150_150 as s3Uri
            ')
            ->join(
                Resident::class,
                'r',
                Join::WITH,
                'r = i.resident'
            )
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByUserIds($ids)
    {
        $qb = $this
            ->createQueryBuilder('i')
            ->select('
                u.id as id, 
                i.type as type,
                i.s3Id_150_150 as s3Id
            ')
            ->join(
                User::class,
                'u',
                Join::WITH,
                'u = i.user'
            )
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->getQuery()
            ->getResult();
    }
}