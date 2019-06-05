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
}