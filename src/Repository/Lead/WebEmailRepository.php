<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Facility;
use App\Entity\Lead\EmailReviewType;
use App\Entity\Lead\WebEmail;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class WebEmailRepository
 * @package App\Repository\Lead
 */
class WebEmailRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->from(WebEmail::class, 'we')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = we.space'
            )
            ->leftJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = we.facility'
            )
            ->leftJoin(
                EmailReviewType::class,
                'ert',
                Join::WITH,
                'ert = we.emailReviewType'
            )
            ->leftJoin(
                User::class,
                'u',
                Join::WITH,
                'u = we.updatedBy'
            );

        if ($space !== null) {
            $queryBuilder
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('we.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->addOrderBy('we.date', 'DESC')
            ->addOrderBy('ert.title', 'ASC');

        $queryBuilder
            ->groupBy('we.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null)
    {
        $qb = $this
            ->createQueryBuilder('we')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = we.space'
            );

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('we.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $qb
            ->addOrderBy('we.date', 'DESC')
            ->addOrderBy('ert.title', 'ASC');

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
            ->createQueryBuilder('we')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = we.space'
            )
            ->where('we.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('we.id IN (:grantIds)')
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
            ->createQueryBuilder('we')
            ->where('we.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = we.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('we.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('we.id')
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
            ->createQueryBuilder('we')
            ->select('we.date');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('we.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('we.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('we.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = we.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('we.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getNotSpamWebEmailList(Space $space = null, array $entityGrants = null, $startDate, $endDate)
    {
        $title = 'spam';

        $qb = $this
            ->createQueryBuilder('we')
            ->select(
                'we.id as id',
                'we.date as date',
                'we.subject as subject',
                'we.name as name',
                'we.email as webEmail',
                'we.phone as phone',
                'we.message as message',
                'f.name as facility',
                'ert.title as review',
                'u.id as uId',
                'u.enabled as enabled',
                'u.email as email',
                'u.firstName as firstName',
                'u.lastName as lastName',
                's.name as space'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = we.space'
            )
            ->leftJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = we.facility'
            )
            ->leftJoin(
                EmailReviewType::class,
                'ert',
                Join::WITH,
                'ert = we.emailReviewType'
            )
            ->leftJoin(
                User::class,
                'u',
                Join::WITH,
                'u = we.updatedBy'
            )
            ->where('we.date >= :startDate')->setParameter('startDate', $startDate)
            ->andWhere('we.date < :endDate')->setParameter('endDate', $endDate)
            ->andWhere('ert.id IS NULL OR (ert.id IS NOT NULL AND ert.title != :title)')->setParameter('title', $title);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('we.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $date
     * @return mixed
     */
    public function getNotReviewedWebEmailList(Space $space = null, array $entityGrants = null, $date)
    {
        $qb = $this
            ->createQueryBuilder('we')
            ->select(
                'we.id as id',
                'we.date as date',
                'we.subject as subject',
                'we.name as name',
                'we.email as webEmail',
                'we.phone as phone',
                'we.message as message',
                'f.name as facility',
                'ert.title as review',
                'u.id as uId',
                'u.enabled as enabled',
                'u.email as email',
                'u.firstName as firstName',
                'u.lastName as lastName',
                's.name as space'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = we.space'
            )
            ->leftJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = we.facility'
            )
            ->leftJoin(
                EmailReviewType::class,
                'ert',
                Join::WITH,
                'ert = we.emailReviewType'
            )
            ->leftJoin(
                User::class,
                'u',
                Join::WITH,
                'u = we.updatedBy'
            )
            ->andWhere('we.date < :date')->setParameter('date', $date)
            ->andWhere('ert.id IS NULL');

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('we.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getNotEmailedWebEmailList(Space $space = null, array $entityGrants = null, $startDate, $endDate)
    {
        $qb = $this
            ->createQueryBuilder('we')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = we.space'
            )
            ->leftJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = we.facility'
            )
            ->leftJoin(
                EmailReviewType::class,
                'ert',
                Join::WITH,
                'ert = we.emailReviewType'
            )
            ->leftJoin(
                User::class,
                'u',
                Join::WITH,
                'u = we.updatedBy'
            )
            ->where('we.date >= :startDate')->setParameter('startDate', $startDate)
            ->andWhere('we.date < :endDate')->setParameter('endDate', $endDate)
            ->andWhere('we.emailed = 0');

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('we.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
