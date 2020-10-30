<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Facility;
use App\Entity\Lead\CareType;
use App\Entity\Lead\FunnelStage;
use App\Entity\Lead\Lead;
use App\Entity\Lead\LeadFunnelStage;
use App\Entity\Lead\StageChangeReason;
use App\Entity\Space;
use App\Entity\User;
use App\Model\Lead\State;
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

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @return mixed
     */
    public function getOrderedByDate(Space $space = null, array $entityGrants = null, $id)
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
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $startDate
     * @param $endDate
     * @param array|null $typeIds
     * @return int|mixed|string
     */
    public function getClosedLeads(Space $space = null, array $entityGrants = null, $startDate, $endDate, array $typeIds = null)
    {
        $qb = $this
            ->createQueryBuilder('lfs')
            ->select(
                "CONCAT(l.firstName, ' ', l.lastName) as leadFullName",
                "CONCAT(l.responsiblePersonFirstName, ' ', l.responsiblePersonLastName) as rpFullName",
                'ct.title as careType',
                "CONCAT(o.firstName, ' ', o.lastName) as ownerFullName",
                'f.name as primaryFacility',
                'lfs.date as date',
                'scr.title as reason',
                'lfs.notes as notes',
                "CONCAT(u.firstName, ' ', u.lastName) as createdByFullName"
            )
            ->innerJoin(
                FunnelStage::class,
                'fs',
                Join::WITH,
                'fs = lfs.stage'
            )
            ->innerJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = lfs.lead'
            )
            ->innerJoin(
                User::class,
                'o',
                Join::WITH,
                'o = l.owner'
            )
            ->leftJoin(
                CareType::class,
                'ct',
                Join::WITH,
                'ct = l.careType'
            )
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = l.primaryFacility'
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
            )
            ->where('lfs.date >= :startDate AND lfs.date <= :endDate AND l.state = :state')
            ->andWhere('l.spam = 0')
            ->andWhere('lfs.date = (SELECT MAX(mfs.date) FROM App:Lead\LeadFunnelStage mfs JOIN mfs.lead ml WHERE ml.id = l.id GROUP BY ml.id)')
            ->setParameter('state', State::TYPE_CLOSED)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($typeIds) {
            $qb
                ->andWhere('f.id IN (:typeIds)')
                ->setParameter('typeIds', $typeIds);
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
            ->groupBy('l.id')
            ->orderBy('lfs.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
