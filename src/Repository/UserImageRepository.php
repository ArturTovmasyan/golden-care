<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class UserImageRepository
 * @package App\Repository
 */
class UserImageRepository extends EntityRepository
{
    /**
     * @param $id
     * @return mixed
     */
    public function getBy($id)
    {
        $qb = $this
            ->createQueryBuilder('ui')
            ->innerJoin(
                User::class,
                'u',
                Join::WITH,
                'u = ui.user'
            )
            ->where('u.id = :id')
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
            ->createQueryBuilder('ui')
            ->select('
                u.id as id, 
                ui.photo_150_150 as photo_150_150
            ')
            ->join(
                User::class,
                'u',
                Join::WITH,
                'u = ui.user'
            )
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->getQuery()
            ->getResult();
    }
}