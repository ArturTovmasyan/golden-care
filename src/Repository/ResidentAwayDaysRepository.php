<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Resident;
use App\Entity\ResidentAdmission;
use App\Entity\ResidentAwayDays;
use App\Entity\ResidentLedger;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentAwayDaysRepository
 * @package App\Repository
 */
class ResidentAwayDaysRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->from(ResidentAwayDays::class, 'rad')
            ->innerJoin(
                ResidentLedger::class,
                'rl',
                Join::WITH,
                'rl = rad.ledger'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rl.resident'
            );

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('rad.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->orderBy('rad.start', 'DESC')
            ->groupBy('rad.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('rad')
            ->innerJoin(
                ResidentLedger::class,
                'rl',
                Join::WITH,
                'rl = rad.ledger'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rl.resident'
            )
            ->where('rl.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rad.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('rad.start', 'DESC')
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
            ->createQueryBuilder('rad')
            ->innerJoin(
                ResidentLedger::class,
                'rl',
                Join::WITH,
                'rl = rad.ledger'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rl.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('rad.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rad.id IN (:grantIds)')
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
            ->createQueryBuilder('rad')
            ->where('rad.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ResidentLedger::class,
                    'rl',
                    Join::WITH,
                    'rl = rad.ledger'
                )
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rl.resident'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rad.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('rad.id')
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
            ->createQueryBuilder('rad')
            ->select('rad.start');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('rad.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('rad.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('rad.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ResidentLedger::class,
                    'rl',
                    Join::WITH,
                    'rl = rad.ledger'
                )
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rl.resident'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rad.id IN (:grantIds)')
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
     * @param $startDate
     * @param $endDate
     * @return int|mixed|string
     */
    public function getByInterval(Space $space = null, array $entityGrants = null, $id, $startDate, $endDate)
    {
        $qb = $this
            ->createQueryBuilder('rad')
            ->innerJoin(
                ResidentLedger::class,
                'rl',
                Join::WITH,
                'rl = rad.ledger'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rl.resident'
            )
            ->where('rl.id = :id')
            ->andWhere('rad.date >= :startDate AND rad.date <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rad.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('rad.start', 'DESC')
            ->getQuery()
            ->getResult();
    }

    ///////////// For Calendar /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @param null $dateFrom
     * @param null $dateTo
     * @return mixed
     */
    public function getResidentCalendarData(Space $space = null, array $entityGrants = null, $id, $dateFrom = null, $dateTo = null)
    {
        $qb = $this->createQueryBuilder('rad');

        $qb
            ->select(
                'rad.id AS id',
                'rl.createdAt AS created_at',
                'rad.start AS start',
                'rad.end AS end',
                'rad.reason AS reason'
            )
            ->join('rad.ledger', 'rl')
            ->join('rl.resident', 'r')
            ->where('r.id=:id')
            ->setParameter('id', $id);

        if ($dateFrom !== null) {
            $qb
                ->andWhere('rad.start >= :start')
                ->andWhere('rad.end >= :start')
                ->setParameter('start', $dateFrom);
        }

        if ($dateTo !== null) {
            $qb
                ->andWhere('rad.start <= :end')
                ->setParameter('end', $dateTo);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rad.id IN (:grantIds)')
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
     * @param null $dateFrom
     * @param null $dateTo
     * @return mixed
     */
    public function getResidentsCalendarData(Space $space = null, array $entityGrants = null, $ids, $dateFrom = null, $dateTo = null)
    {
        $qb = $this->createQueryBuilder('rad');

        $qb
            ->select(
                'rad.id AS id',
                'rl.createdAt AS created_at',
                'rad.start AS start',
                'rad.end AS end',
                'rad.reason AS reason',
                'r.id AS resident_id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'fr.number AS room_number',
                'fb.number AS bed_number'
            )
            ->join('rad.ledger', 'rl')
            ->join('rl.resident', 'r')
            ->innerJoin(
                ResidentAdmission::class,
                'ra',
                Join::WITH,
                'ra.resident = r'
            )
            ->join('ra.facilityBed', 'fb')
            ->join('fb.room', 'fr')
            ->where('r.id IN (:ids)')
            ->andWhere('ra.end IS NULL')
            ->setParameter('ids', $ids);

        if ($dateFrom !== null) {
            $qb
                ->andWhere('rad.start >= :start')
                ->andWhere('rad.end >= :start')
                ->setParameter('start', $dateFrom);
        }

        if ($dateTo !== null) {
            $qb
                ->andWhere('rad.start <= :end')
                ->setParameter('end', $dateTo);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rad.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
    ///////////// End For Calendar /////////////////////////////////////////////////////////////////////////////////////
}