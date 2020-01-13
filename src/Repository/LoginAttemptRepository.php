<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class LoginAttemptRepository
 * @package App\Repository
 */
class LoginAttemptRepository extends EntityRepository
{
    /**
     * @param $login
     * @param $ip
     * @return mixed
     */
    public function getAttemptsCount($login, $ip)
    {
        $date = new \DateTime();
        $date->modify('-30 minutes');

        return $this->createQueryBuilder('la')
            ->select('count(la.id)')
            ->where('la.login =:login AND la.ip =:ip AND la.createdAt > :date')
            ->setParameter('login', $login)
            ->setParameter('ip', $ip)
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }
}