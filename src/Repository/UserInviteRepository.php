<?php

namespace App\Repository;

use App\Entity\Space;
use App\Entity\User;
use App\Entity\UserInvite;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class UserInviteRepository
 * @package App\Repository
 */
class UserInviteRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->from(UserInvite::class, 'ui')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ui.space'
            )
            ->innerJoin(
                User::class,
                'u',
                Join::WITH,
                'u = ui.user'
            );

        if ($space !== null) {
            $queryBuilder
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('ui.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('ui.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null)
    {
        $qb = $this
            ->createQueryBuilder('ui')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ui.space'
            );

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ui.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @return mixed
     */
    public function getOne(Space $space = null, array $entityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('ui')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ui.space'
            )
            ->where('ui.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ui.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param array $criteria
     * @return array|mixed
     */
    public function getUserInviteSpaceAndOwnerCriteria(array $criteria)
    {
        if (1 == $criteria['owner']) {
            return $this->createQueryBuilder('ui')
                ->andWhere('ui.space = :space')
                ->setParameter('space', $criteria['space'])
                ->andWhere('ui.owner = :owner')
                ->setParameter('owner', $criteria['owner'])
                ->getQuery()
                ->getResult();
        }

        return [];
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
            ->createQueryBuilder('ui')
            ->where('ui.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ui.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ui.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('ui.id')
            ->getQuery()
            ->getResult();
    }
}
