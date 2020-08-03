<?php

namespace App\Repository;

use App\Entity\Space;
use App\Entity\User;
use App\Entity\UserLog;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class UserLogRepository
 * @package App\Repository
 */
class UserLogRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @return mixed
     */
    public function getUserLoginActivity(Space $space = null)
    {
        $now = new \DateTime('now');
        $previousDate = clone $now;
        $date = date_modify($previousDate, '-7 day');

        $qb = $this
            ->createQueryBuilder('ul')
            ->select('
                u.id as id,
                u.firstName as firstName,
                u.lastName as lastName,
                ul.createdAt as createdAt
            ')
            ->addSelect("GROUP_CONCAT(r.name SEPARATOR ', ') AS roles")
            ->addSelect(
                "(SELECT GROUP_CONCAT(DISTINCT f.shorthand SEPARATOR ', ')
                        FROM
                          App\\Entity\\Facility f
                        WHERE JSON_CONTAINS(
                            JSON_EXTRACT(
                              u.grants,
                              '$.\"persistence-facility\"'
                            ),
                            CAST(f.id AS JSON)
                          ) = 1) AS facilityNames")
            ->innerJoin(
                User::class,
                'u',
                Join::WITH,
                'u = ul.user'
            )
            ->innerJoin('u.roles', 'r')
            ->andWhere('ul.type = :type AND ul.createdAt >= :date AND ul.createdAt < :now')
            ->setParameter('type', UserLog::LOG_TYPE_AUTHENTICATION)
            ->setParameter('date', $date)
            ->setParameter('now', $now);

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

        return $qb->groupBy('ul.id')
            ->orderBy('u.firstName')
            ->addOrderBy('ul.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}