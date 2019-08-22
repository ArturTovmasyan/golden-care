<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\ChangeLog;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ChangeLogRepository
 * @package App\Repository
 */
class ChangeLogRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(ChangeLog::class, 'cl')
            ->addSelect("JSON_EXTRACT(cl.content, '$.owner') AS title")
            ->addSelect("JSON_EXTRACT(cl.content, '$.created_at') AS log_time")
            ->addSelect("JSON_EXTRACT(cl.content, '$.user_name') AS updated_by")
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = cl.space'
            )
            ->leftJoin(
                User::class,
                'o',
                Join::WITH,
                'o = cl.owner'
            );

        if ($space !== null) {
            $queryBuilder
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('cl.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->orderBy('cl.createdAt', 'DESC')
            ->groupBy('cl.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null)
    {
        $qb = $this
            ->createQueryBuilder('cl')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = cl.space'
            );

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('cl.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('cl.createdAt', 'DESC')
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
            ->createQueryBuilder('cl')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = cl.space'
            )
            ->where('cl.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('cl.id IN (:grantIds)')
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
            ->createQueryBuilder('cl')
            ->where('cl.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = cl.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('cl.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('cl.id')
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
            ->createQueryBuilder('cl')
            ->select('cl.id');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('cl.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('cl.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('cl.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = cl.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('cl.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return mixed
     */
    public function getChangeLogsForCrontabNotification(Space $space = null, array $entityGrants = null)
    {
        // get - 24 hr
        $date = new \DateTime();
        $date->modify('-24 hours');

        $qb = $this->createQueryBuilder('cl')
            ->select('cl')
            ->where("cl.createdAt >= '" . $date->format('Y-m-d 06:00:00') . "'");

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = cl.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('cl.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('cl.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}