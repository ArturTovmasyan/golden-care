<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\FacilityDashboard;
use App\Entity\Facility;
use App\Entity\Space;
use App\Util\Common\ImtDateTimeInterval;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityDashboardRepository
 * @package App\Repository
 */
class FacilityDashboardRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(FacilityDashboard::class, 'fd')
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = fd.facility'
            );

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = f.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('fd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $queryBuilder
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        $queryBuilder
            ->groupBy('fd.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @param ImtDateTimeInterval $interval
     * @param null $facilityId
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null, ImtDateTimeInterval $interval = null, $facilityId = null)
    {
        $qb = $this
            ->createQueryBuilder('fd')
            ->select(
                'fd.id as id',
                'f.id as facilityId',
                'fd.date as date',
                'fd.totalCapacity as totalCapacity',
                'fd.breakEven as breakEven',
                'fd.capacityYellow as capacityYellow',
                'fd.occupancy as occupancy',
                'fd.moveInsRespite as moveInsRespite',
                'fd.moveInsLongTerm as moveInsLongTerm',
                'fd.moveOutsRespite as moveOutsRespite',
                'fd.moveOutsLongTerm as moveOutsLongTerm'
            )
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = fd.facility'
            );

        if ($interval !== null) {
            $qb
                ->andWhere('fd.date >= :dateFrom AND fd.date <= :dateTo')
                ->setParameter('dateFrom', $interval->getStart())
                ->setParameter('dateTo', $interval->getEnd());
        }

        if ($facilityId !== null) {
            $qb
                ->andWhere('f.id = :facilityId')
                ->setParameter('facilityId', $facilityId);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = f.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @param $id
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('fd')
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = fd.facility'
            )
            ->where('f.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = f.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @param $id
     * @return mixed
     */
    public function getOne(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('fd')
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = fd.facility'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = f.space'
            )
            ->where('fd.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @param $ids
     * @return mixed
     */
    public function findByIds(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null, $ids)
    {
        $qb = $this
            ->createQueryBuilder('fd')
            ->where('fd.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Facility::class,
                    'f',
                    Join::WITH,
                    'f = fd.facility'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = f.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        return $qb->groupBy('fd.id')
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
            ->createQueryBuilder('fd')
            ->select('fd.id');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('fd.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('fd.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('fd.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Facility::class,
                    'f',
                    Join::WITH,
                    'f = fd.facility'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = f.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}