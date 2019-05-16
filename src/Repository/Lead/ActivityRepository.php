<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Facility;
use App\Entity\Lead\ActivityStatus;
use App\Entity\Lead\Activity;
use App\Entity\Lead\ActivityType;
use App\Entity\Lead\Organization;
use App\Entity\Lead\Referral;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ActivityRepository
 * @package App\Repository\Lead
 */
class ActivityRepository extends EntityRepository  implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(Activity::class, 'a')
            ->innerJoin(
                ActivityType::class,
                'at',
                Join::WITH,
                'at = a.type'
            )
            ->leftJoin(
                ActivityStatus::class,
                'st',
                Join::WITH,
                'st = a.status'
            )
            ->leftJoin(
                User::class,
                'u',
                Join::WITH,
                'u = a.assignTo'
            )
            ->leftJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = a.facility'
            )
            ->leftJoin(
                Referral::class,
                'r',
                Join::WITH,
                'r = a.referral'
            )
            ->leftJoin(
                Organization::class,
                'o',
                Join::WITH,
                'o = a.organization'
            );

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    ActivityStatus::class,
                    'ds',
                    Join::WITH,
                    'ds = at.defaultStatus'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ds.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('at.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null)
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->innerJoin(
                ActivityType::class,
                'at',
                Join::WITH,
                'at = a.type'
            );

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ActivityStatus::class,
                    'ds',
                    Join::WITH,
                    'ds = at.defaultStatus'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ds.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $qb
            ->addOrderBy('a.date', 'DESC');

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
            ->createQueryBuilder('a')
            ->innerJoin(
                ActivityType::class,
                'at',
                Join::WITH,
                'at = a.type'
            )
            ->innerJoin(
                ActivityStatus::class,
                'ds',
                Join::WITH,
                'ds = at.defaultStatus'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ds.space'
            )
            ->where('a.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
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
            ->createQueryBuilder('a')
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ActivityType::class,
                    'at',
                    Join::WITH,
                    'at = a.type'
                )
                ->innerJoin(
                    ActivityStatus::class,
                    'ds',
                    Join::WITH,
                    'ds = at.defaultStatus'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ds.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('a.id')
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
            ->createQueryBuilder('a')
            ->select('a.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('a.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('a.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('a.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ActivityType::class,
                    'at',
                    Join::WITH,
                    'at = a.type'
                )
                ->innerJoin(
                    ActivityStatus::class,
                    'ds',
                    Join::WITH,
                    'ds = at.defaultStatus'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ds.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
