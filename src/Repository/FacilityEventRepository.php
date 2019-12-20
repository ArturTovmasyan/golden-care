<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\EventDefinition;
use App\Entity\Facility;
use App\Entity\FacilityEvent;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityEventRepository
 * @package App\Repository
 */
class FacilityEventRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(FacilityEvent::class, 'fe')

            ->addSelect("
                JSON_ARRAY(                    
                    JSON_OBJECT('User(s)', JSON_ARRAYAGG(
                            CONCAT(
                                COALESCE(u.firstName, ''),
                                ' ',
                                COALESCE(u.lastName, '')
                            )
                        )
                    )
                ) as users
            ")
            ->addSelect("
                JSON_ARRAY(                    
                    JSON_OBJECT('Resident(s)', JSON_ARRAYAGG(
                            CONCAT(
                                COALESCE(r.firstName, ''),
                                ' ',
                                COALESCE(r.lastName, '')
                            )
                        )
                    )
                ) as residents
            ")

            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = fe.facility'
            )
            ->innerJoin(
                EventDefinition::class,
                'ed',
                Join::WITH,
                'ed = fe.definition'
            )
            ->leftJoin(
                'fe.users',
                'u'
            )
            ->leftJoin(
                'fe.residents',
                'r'
            )
        ;

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
                ->andWhere('fe.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->orderBy('fe.start', 'DESC')
            ->groupBy('fe.id');
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
            ->createQueryBuilder('fe')
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = fe.facility'
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
                ->andWhere('fe.id IN (:grantIds)')
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
            ->createQueryBuilder('fe')
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = fe.facility'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = f.space'
            )
            ->where('fe.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fe.id IN (:grantIds)')
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
            ->createQueryBuilder('fe')
            ->where('fe.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Facility::class,
                    'f',
                    Join::WITH,
                    'f = fe.facility'
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
                ->andWhere('fe.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('fe.id')
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
            ->createQueryBuilder('fe')
            ->innerJoin(
                EventDefinition::class,
                'ed',
                Join::WITH,
                'ed = fe.definition'
            )
            ->select('fe.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('fe.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('fe.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('fe.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Facility::class,
                    'f',
                    Join::WITH,
                    'f = fe.facility'
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
                ->andWhere('fe.id IN (:grantIds)')
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
    public function getFacilityCalendarData(Space $space = null, array $entityGrants = null, $id, $dateFrom = null, $dateTo = null)
    {
        $qb = $this->createQueryBuilder('fe');

        $qb
            ->select(
                'fe.id AS id',
                'fe.title AS title',
                'fe.start AS start',
                'fe.end AS end',
                'fe.notes AS notes'
            )
            ->join('fe.facility', 'f')
            ->join('fe.definition', 'd')
            ->where('f.id=:id')
            ->setParameter('id', $id);

        if ($dateFrom !== null) {
            $qb
                ->andWhere('fe.start >= :start')
                ->andWhere('fe.end IS NULL OR fe.end >= :start')
                ->setParameter('start', $dateFrom);
        }

        if ($dateTo !== null) {
            $qb
                ->andWhere('fe.start <= :end')
                ->setParameter('end', $dateTo);
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
                ->andWhere('fe.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array $ids
     * @param null $dateFrom
     * @param null $dateTo
     * @return mixed
     */
    public function getFacilityCalendarDataByResident(Space $space = null, array $entityGrants = null, array $ids, $dateFrom = null, $dateTo = null)
    {
        $qb = $this->createQueryBuilder('fe');

        $qb
            ->select(
                'fe.id AS id',
                'fe.title AS title',
                'fe.start AS start',
                'fe.end AS end',
                'fe.notes AS notes',
                'fe.rsvp AS rsvp',
                'f.id AS facility_id',
                'f.name AS facility_name'
            )
            ->join('fe.facility', 'f')
            ->join('fe.definition', 'd')
            ->join('fe.residents', 'r')
            ->andWhere('r.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($dateFrom !== null) {
            $qb
                ->andWhere('fe.start >= :start')
                ->andWhere('fe.end IS NULL OR fe.end >= :start')
                ->setParameter('start', $dateFrom);
        }

        if ($dateTo !== null) {
            $qb
                ->andWhere('fe.start <= :end')
                ->setParameter('end', $dateTo);
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
                ->andWhere('fe.id IN (:grantIds)')
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
     * @return mixed
     */
    public function getActivitiesForCrontabNotification(Space $space = null, array $entityGrants = null)
    {
        $today = new \DateTime('now');
        $tomorrow = date_modify($today, '+1 day');
        $tomorrowStart = $tomorrow->format('Y-m-d 00:00:00');
        $tomorrowEnd = $tomorrow->format('Y-m-d 23:59:59');
        $qb = $this->createQueryBuilder('fe')
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = fe.facility'
            )
            ->innerJoin(
                EventDefinition::class,
                'ed',
                Join::WITH,
                'ed = fe.definition'
            )
            ->join('fe.users', 'u')
            ->where('fe.start <= :tomorrowEnd AND fe.start >= :tomorrowStart')
            ->setParameter('tomorrowStart', $tomorrowStart)
            ->setParameter('tomorrowEnd', $tomorrowEnd);

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
                ->andWhere('fe.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}