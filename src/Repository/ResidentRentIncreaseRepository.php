<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Resident;
use App\Entity\ResidentAdmission;
use App\Entity\ResidentRentIncrease;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentRentIncreaseRepository
 * @package App\Repository
 */
class ResidentRentIncreaseRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(ResidentRentIncrease::class, 'rri')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rri.resident'
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
                ->andWhere('rri.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('rri.id');
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
            ->createQueryBuilder('rri')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rri.resident'
            )
            ->where('r.id = :id')
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
                ->andWhere('rri.id IN (:grantIds)')
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
            ->createQueryBuilder('rri')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rri.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('rri.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rri.id IN (:grantIds)')
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
            ->createQueryBuilder('rri')
            ->where('rri.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rri.resident'
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
                ->andWhere('rri.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('rri.id')
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
            ->createQueryBuilder('rri')
            ->select('rri.amount');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('rri.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('rri.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('rri.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rri.resident'
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
                ->andWhere('rri.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
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
        $qb = $this->createQueryBuilder('rri');

        $qb
            ->select(
                'rri.id AS id',
                'rri.amount AS amount',
                'rri.reason AS reason',
                'rri.effectiveDate AS start'
            )
            ->join('rri.resident', 'r')
            ->where('r.id=:id')
            ->setParameter('id', $id);

        if ($dateFrom !== null) {
            $qb
                ->andWhere('rri.effectiveDate >= :start')
                ->setParameter('start', $dateFrom);
        }

        if ($dateTo !== null) {
            $qb
                ->andWhere('rri.effectiveDate <= :end')
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
                ->andWhere('rri.id IN (:grantIds)')
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
        $qb = $this->createQueryBuilder('rri');

        $qb
            ->select(
                'rri.id AS id',
                'rri.amount AS amount',
                'rri.reason AS reason',
                'rri.effectiveDate AS start',
                'r.id AS resident_id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'fr.number AS room_number',
                'fb.number AS bed_number'
            )
            ->join('rri.resident', 'r')
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
                ->andWhere('rri.effectiveDate >= :start')
                ->setParameter('start', $dateFrom);
        }

        if ($dateTo !== null) {
            $qb
                ->andWhere('rri.effectiveDate <= :end')
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
                ->andWhere('rri.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
    ///////////// End For Calendar /////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getRentIncreasesForCronJob(Space $space = null, array $entityGrants = null, $startDate, $endDate)
    {
        $qb = $this->createQueryBuilder('rri')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rri.resident'
            )
            ->where('rri.effectiveDate <= :endDate AND rri.effectiveDate >= :startDate')
            ->andWhere('rri.effectiveDate = (SELECT MAX(mri.effectiveDate) FROM App:ResidentRentIncrease mri JOIN mri.resident mr WHERE mr.id = r.id GROUP BY mr.id)')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

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
                ->andWhere('rri.id IN (:grantIds)')
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
    public function getRentIncreasesForCronJobNotification(Space $space = null, array $entityGrants = null)
    {
        $now = new \DateTime('now');
        $startDate = $now->format('Y-m-d 00:00:00');
        $endDate = $now->format('Y-m-d 23:59:59');
        $qb = $this->createQueryBuilder('rri')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rri.resident'
            )
            ->where('rri.notificationDate <= :endDate AND rri.notificationDate >= :startDate')
            ->andWhere('rri.effectiveDate = (SELECT MAX(mri.effectiveDate) FROM App:ResidentRentIncrease mri JOIN mri.resident mr WHERE mr.id = r.id GROUP BY mr.id)')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

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
                ->andWhere('rri.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}