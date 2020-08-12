<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Lead\ActivityStatus;
use App\Entity\Lead\ActivityType;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ActivityTypeRepository
 * @package App\Repository\Lead
 */
class ActivityTypeRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->from(ActivityType::class, 'at')
            ->addSelect('SC_ACTIVITY_TYPE_CATEGORY_DECORATOR(at.categories) AS categories')
            ->innerJoin(
                ActivityStatus::class,
                'ds',
                Join::WITH,
                'ds = at.defaultStatus'
            );

        if ($space !== null) {
            $queryBuilder
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
                ->andWhere('at.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('at.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param null $category
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null, $category = null)
    {
        $qb = $this
            ->createQueryBuilder('at')
            ->innerJoin(
                ActivityStatus::class,
                'ds',
                Join::WITH,
                'ds = at.defaultStatus'
            );

        if ($category !== null) {
            $qb
                ->andWhere("JSON_CONTAINS(at.categories, :category, '$') = 1")
                ->setParameter('category', $category);
        }

        if ($space !== null) {
            $qb
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
                ->andWhere('at.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $qb
            ->addOrderBy('at.title', 'ASC');

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
            ->createQueryBuilder('at')
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
            ->where('at.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('at.id IN (:grantIds)')
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
            ->createQueryBuilder('at')
            ->where('at.id IN (:ids)')
            ->setParameter('ids', $ids);

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
                ->andWhere('at.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('at.id')
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
            ->createQueryBuilder('at')
            ->select('at.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('at.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('at.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('at.id IN (:array)')
                ->setParameter('array', []);
        }

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
                ->andWhere('at.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param $title
     * @return mixed
     */
    public function getByTitle(Space $space = null, $title)
    {
        $qb = $this
            ->createQueryBuilder('at')
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
            ->andWhere("at.title LIKE '%{$title}%'");

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }
}
