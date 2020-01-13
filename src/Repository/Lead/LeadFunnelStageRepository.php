<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Lead\FunnelStage;
use App\Entity\Lead\Lead;
use App\Entity\Lead\LeadFunnelStage;
use App\Entity\Lead\StageChangeReason;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class LeadFunnelStageRepository
 * @package App\Repository\Lead
 */
class LeadFunnelStageRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->from(LeadFunnelStage::class, 'lfs')
            ->innerJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = lfs.lead'
            )
            ->innerJoin(
                FunnelStage::class,
                'fs',
                Join::WITH,
                'fs = lfs.stage'
            )
            ->leftJoin(
                StageChangeReason::class,
                'scr',
                Join::WITH,
                'scr = lfs.reason'
            )
            ->leftJoin(
                User::class,
                'u',
                Join::WITH,
                'u = lfs.createdBy'
            );

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = fs.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('lfs.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('lfs.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null)
    {
        $qb = $this
            ->createQueryBuilder('lfs')
            ->innerJoin(
                FunnelStage::class,
                'fs',
                Join::WITH,
                'fs = lfs.stage'
            );

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = fs.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('lfs.id IN (:grantIds)')
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
            ->createQueryBuilder('lfs')
            ->innerJoin(
                FunnelStage::class,
                'fs',
                Join::WITH,
                'fs = lfs.stage'
            )
            ->where('lfs.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = fs.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('lfs.id IN (:grantIds)')
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
            ->createQueryBuilder('lfs')
            ->where('lfs.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    FunnelStage::class,
                    'fs',
                    Join::WITH,
                    'fs = lfs.stage'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = fs.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('lfs.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('lfs.id')
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
            ->createQueryBuilder('lfs')
            ->innerJoin(
                FunnelStage::class,
                'fs',
                Join::WITH,
                'fs = lfs.stage'
            )
            ->select('fs.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('lfs.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('lfs.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('lfs.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = fs.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('lfs.id IN (:grantIds)')
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
    public function getLastAction(Space $space = null, array $entityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('lfs')
            ->join('lfs.lead', 'l')
            ->where('l.id=:id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    FunnelStage::class,
                    'fs',
                    Join::WITH,
                    'fs = lfs.stage'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = fs.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('lfs.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('lfs.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
