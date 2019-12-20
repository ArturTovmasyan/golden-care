<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\CorporateEvent;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class CorporateEventUserRepository
 * @package App\Repository
 */
class CorporateEventUserRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $event
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, $event)
    {
        $qb = $this
            ->createQueryBuilder('ceu')
            ->innerJoin(
                CorporateEvent::class,
                'e',
                Join::WITH,
                'e = ceu.event'
            )
            ->where('e = :event')
            ->setParameter('event', $event);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    User::class,
                    'u',
                    Join::WITH,
                    'u = ceu.user'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = u.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ceu.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $ids
     * @return mixed
     */
    public function findByIds(Space $space = null, array $entityGrants = null, $ids)
    {
        $qb = $this
            ->createQueryBuilder('ceu')
            ->where('ceu.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    User::class,
                    'u',
                    Join::WITH,
                    'u = ceu.user'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = u.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ceu.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('ceu.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param null $mappedBy
     * @param null $id
     * @param array|null $ids
     * @return mixed
     */
    public function getRelatedData(Space $space = null, array $entityGrants = null, $mappedBy = null, $id = null, array $ids = null)
    {
        $qb = $this
            ->createQueryBuilder('ceu')
            ->innerJoin(
                User::class,
                'u',
                Join::WITH,
                'u = ceu.user'
            )
            ->select("CONCAT(u.firstName, ' ', u.lastName) as fullName");

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('ceu.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('ceu.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('ceu.id IN (:array)')
                ->setParameter('array', []);
        }

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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ceu.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}