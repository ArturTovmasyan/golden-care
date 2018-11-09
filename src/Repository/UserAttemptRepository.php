<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * Class UserAttemptRepository
 * @package App\Repository
 */
class UserAttemptRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param $ip
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAttemptsCount(User $user, $ip)
    {
        $date = new \DateTime();
        $date->modify('-30 minutes');

        return $this->createQueryBuilder('ua')
            ->select('count(ua.id)')
            ->where('ua.user =:user AND ua.ip =:ip AND ua.createdAt > :date')
            ->setParameter('user', $user)
            ->setParameter('ip', $ip)
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }
}