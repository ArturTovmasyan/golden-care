<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\UserNotFoundException;
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTotalCount()
    {
        return $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

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

    /**
     * @param Space $space
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findTotalUsersBySpace(Space $space)
    {
        return $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->innerJoin(
                SpaceUser::class,
                'su',
                Join::WITH,
                'su.user = u'
            )
            ->where('su.space = :space AND su.status = :status')
            ->setParameter('space', $space)
            ->setParameter('status', \App\Model\SpaceUserRole::STATUS_ACCEPTED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Space $space
     * @param $userId
     * @return mixed
     */
    public function findUserBySpaceAndId(Space $space, $userId)
    {
        try {
            return $this->createQueryBuilder('u')
                ->innerJoin(
                    SpaceUser::class,
                    'su',
                    Join::WITH,
                    'su.user = u'
                )
                ->where('su.space = :space AND su.status = :status AND u.id = :user_id')
                ->setParameter('space', $space)
                ->setParameter('status', \App\Model\SpaceUserRole::STATUS_ACCEPTED)
                ->setParameter('user_id', $userId)
                ->getQuery()
                ->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException | \Doctrine\ORM\NonUniqueResultException $e) {
            throw new UserNotFoundException();
        }
    }
}