<?php

namespace App\Repository;

use App\Entity\Space;
use App\Entity\SpaceUser;
use App\Entity\SpaceUserRole;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Class UserRepository
 * @package App\Repository
 */
class UserRepository extends EntityRepository
{
    /**
     * @param $username
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUserByUsername($username)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $username
     * @param $email
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUserByUsernameOrEmail($username, $email)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $space
     * @return mixed
     */
    public function findUsersBySpace(Space $space)
    {
        return $this->createQueryBuilder('u')
            ->innerJoin(
                SpaceUser::class,
                'su',
                Join::WITH,
                'su.user = u'
            )
            ->where('su.space = :space AND su.status = :status')
            ->setParameter('space', $space)
            ->setParameter('status', \App\Model\SpaceUserRole::STATUS_ACCEPTED)
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }
}