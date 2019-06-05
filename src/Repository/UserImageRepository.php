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
            ->createQueryBuilder('ui')
            ->innerJoin(
                User::class,
                'u',
                Join::WITH,
                'u = ui.user'
            )
            ->where('u.id = :id')
            ->andWhere('ui.id != :originalId')
            ->setParameter('id', $id)
            ->setParameter('originalId', $originalId);

        return $qb
            ->getQuery()
            ->getResult();
    }
}