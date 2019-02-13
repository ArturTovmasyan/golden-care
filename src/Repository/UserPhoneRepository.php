<?php

namespace App\Repository;

use App\Entity\Space;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class UserPhoneRepository
 * @package App\Repository
 */
class UserPhoneRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param User $user
     * @return mixed
     */
    public function getBy(Space $space = null, User $user)
    {
        $qb = $this
            ->createQueryBuilder('up')
            ->innerJoin(
                User::class,
                'u',
                Join::WITH,
                'u = up.user'
            )
            ->where('u = :user')
            ->setParameter('user', $user);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = u.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
