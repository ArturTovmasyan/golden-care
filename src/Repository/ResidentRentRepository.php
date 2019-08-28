<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\CareLevel;
use App\Entity\CityStateZip;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentAdmission;
use App\Entity\ResidentRent;
use App\Entity\Space;
use App\Model\AdmissionType;
use App\Model\GroupType;
use App\Util\Common\ImtDateTimeInterval;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentRentRepository
 * @package App\Repository
 */
class ResidentRentRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(ResidentRent::class, 'rr')
            ->addSelect('SC_PAYMENT_SOURCE_DECORATOR(rr.source) AS info')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rr.resident'
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
                ->andWhere('rr.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('rr.id');
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
            ->createQueryBuilder('rr')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rr.resident'
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
                ->andWhere('rr.id IN (:grantIds)')
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
            ->createQueryBuilder('rr')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rr.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('rr.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rr.id IN (:grantIds)')
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
            ->createQueryBuilder('rr')
            ->where('rr.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rr.resident'
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
                ->andWhere('rr.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('rr.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(Space $space = null, array $entityGrants = null, array $residentIds)
    {
        $qb = $this->createQueryBuilder('rr');

        $qb
            ->select('
                    rr.id as id,
                    rr.start as start,
                    rr.amount as amount,
                    r.id as residentId
            ')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'rr.resident = r'
            )
            ->where('r.id IN (:residentIds)')
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) FROM App:ResidentRent mrr JOIN mrr.resident res GROUP BY res.id)')
            ->setParameter('residentIds', $residentIds);

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
                ->andWhere('rr.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    ////////////////////////////Resident Admission Part///////////////////////////////////////////////////
    /**
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param null $typeId
     * @return QueryBuilder
     */
    public function getResidentAdmissionWithRentQb($type, ImtDateTimeInterval $reportInterval = null, $typeId = null) : QueryBuilder
    {
        /** @var ResidentAdmissionRepository $admissionRepo */
        $admissionRepo = $this
            ->getEntityManager()
            ->getRepository(ResidentAdmission::class);

        /** @var QueryBuilder $qb */
        $qb = $admissionRepo
            ->getResidentAdmissionIntervalQb($reportInterval);

        $qb
            ->from(ResidentRent::class, 'rr')
            ->from(Resident::class, 'r')
            ->andWhere('rr.resident = r')
            ->andWhere('rr.resident = rar')
            ->andWhere('(rr.end IS NULL OR rr.end > = ra.start) AND (ra.end IS NULL OR rr.start < = ra.end)')
            ->andWhere('ra.groupType=:type')
            ->setParameter('type', $type)
            ->select(
                'r.id as id',
                'r.firstName as firstName',
                'r.lastName as lastName',
                'ra.id as actionId',
                'rr.id as rentId',
                'rr.amount as amount',
                'rr.period as period',
                '(CASE WHEN rr.start > = ra.start THEN rr.start ELSE ra.start END) as admitted',
                '(CASE
                    WHEN rr.end IS NULL AND ra.end IS NULL THEN ra.end
                    WHEN ra.end IS NULL THEN rr.end
                    WHEN rr.end IS NULL THEN ra.end
                    WHEN rr.end < ra.end THEN rr.end
                    ELSE ra.end END) as discharged',
                'rr.source as sources'
            );

        if ($reportInterval) {
            $qb
                ->andWhere('rr.end IS NULL OR rr.end > = :start');
            if ($reportInterval->getEnd()) {
                $qb
                    ->andWhere('rr.start < = :end');
            }
        }

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.shorthand as typeShorthand,
                        fr.number as roomNumber,
                        fb.number as bedNumber,
                        fb.id as bedId'
                    )
                    ->innerJoin(
                        FacilityBed::class,
                        'fb',
                        Join::WITH,
                        'ra.facilityBed = fb'
                    )
                    ->innerJoin(
                        FacilityRoom::class,
                        'fr',
                        Join::WITH,
                        'fb.room = fr'
                    )
                    ->innerJoin(
                        Facility::class,
                        'f',
                        Join::WITH,
                        'fr.facility = f'
                    );

                $qb
                    ->orderBy('f.shorthand')
                    ->addOrderBy('fr.number')
                    ->addOrderBy('fb.number');

                if ($typeId) {
                    $qb
                        ->andWhere('f.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            case GroupType::TYPE_APARTMENT:
                $qb
                    ->addSelect(
                        'a.id as typeId,
                        a.name as typeName,
                        a.shorthand as typeShorthand,
                        ar.number as roomNumber,
                        ab.number as bedNumber
                        ab.id as bedId'
                    )
                    ->innerJoin(
                        ApartmentBed::class,
                        'ab',
                        Join::WITH,
                        'ra.apartmentBed = ab'
                    )
                    ->innerJoin(
                        ApartmentRoom::class,
                        'ar',
                        Join::WITH,
                        'ab.room = ar'
                    )
                    ->innerJoin(
                        Apartment::class,
                        'a',
                        Join::WITH,
                        'ar.apartment = a'
                    );

                $qb
                    ->orderBy('a.shorthand')
                    ->addOrderBy('ar.number')
                    ->addOrderBy('ab.number');

                if ($typeId) {
                    $qb
                        ->andWhere('a.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            case GroupType::TYPE_REGION:
                $qb
                    ->addSelect(
                        'reg.id as typeId,
                        reg.name as typeName,
                        reg.shorthand as typeShorthand'
                    )
                    ->innerJoin(
                        Region::class,
                        'reg',
                        Join::WITH,
                        'ra.region = reg'
                    );

                $qb
                    ->orderBy('reg.shorthand');

                if ($typeId) {
                    $qb
                        ->andWhere('reg.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        return $qb;
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param null $typeId
     * @param array|null $notGrantResidentIds
     * @return mixed
     */
    public function getAdmissionRentsWithSources(Space $space = null, array $entityGrants = null, $type, ImtDateTimeInterval $reportInterval = null, $typeId = null, array $notGrantResidentIds = null)
    {
        $qb = $this
            ->getResidentAdmissionWithRentQb($type, $reportInterval, $typeId)
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) 
                        FROM App:ResidentRent mrr 
                        JOIN mrr.resident res 
                        WHERE (mrr.end IS NULL OR mrr.end > = ra.start) AND (ra.end IS NULL OR mrr.start < = ra.end)
                        GROUP BY res.id)'
            );

        if ($reportInterval) {
            if ($reportInterval->getEnd()) {
                $qb
                    ->andWhere('ra.id IN (SELECT MAX(mra.id)
                        FROM App:ResidentAdmission mra
                        JOIN mra.resident mrar
                        WHERE (mra.end IS NULL OR mra.end > = :startDate) AND (mra.start < = :endDate)
                        GROUP BY mrar.id)'
                    )
                    ->setParameter('startDate', $reportInterval->getStart())
                    ->setParameter('endDate', $reportInterval->getEnd());
            } else {
                $qb
                    ->andWhere('ra.id IN (SELECT MAX(mra.id)
                        FROM App:ResidentAdmission mra
                        JOIN mra.resident mrar
                        WHERE (mra.end IS NULL OR mra.end > = :startDate)
                        GROUP BY mrar.id)'
                    )
                    ->setParameter('startDate', $reportInterval->getStart());
            }
        } else {
            $qb
                ->andWhere('ra.id IN (SELECT MAX(mra.id)
                    FROM App:ResidentAdmission mra
                    JOIN mra.resident mrar
                    GROUP BY mrar.id)'
                );
        }

        $qb
            ->andWhere('r.id IN (SELECT ar.id 
                        FROM App:ResidentAdmission ara 
                        JOIN ara.resident ar 
                        WHERE ara.admissionType<'. AdmissionType::DISCHARGE .' AND ara.end IS NULL)'
            );

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rar.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($notGrantResidentIds !== null) {
            $qb
                ->andWhere('r.id NOT IN (:notGrantResidentIds)')
                ->setParameter('notGrantResidentIds', $notGrantResidentIds);
        }

        return $qb
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @param $type
     * @param ImtDateTimeInterval $reportInterval
     * @param null $typeId
     * @return QueryBuilder
     */
    public function getRoomListResidentAdmissionWithRentQb($type, ImtDateTimeInterval $reportInterval, $typeId = null) : QueryBuilder
    {
        /** @var ResidentAdmissionRepository $admissionRepo */
        $admissionRepo = $this
            ->getEntityManager()
            ->getRepository(ResidentAdmission::class);

        /** @var QueryBuilder $qb */
        $qb = $admissionRepo
            ->getRoomListResidentAdmissionIntervalQb($reportInterval);

        $qb
            ->from(ResidentRent::class, 'rr')
            ->from(Resident::class, 'r')
            ->andWhere('rr.resident = r')
            ->andWhere('rr.resident = rar')
            ->andWhere('(rr.start < = :end AND rr.start > = :start) OR (rr.start < :start AND (rr.end IS NULL OR rr.end > :start))')
            ->andWhere('ra.groupType=:type')
            ->setParameter('type', $type)
            ->select(
                'r.id as id',
                'r.firstName as firstName',
                'r.lastName as lastName',
                'ra.id as actionId',
                '(SELECT DISTINCT minra.start FROM App:ResidentAdmission minra
                WHERE minra.resident=r
                AND minra.start = (SELECT MIN(raMin.start) FROM App:ResidentAdmission raMin WHERE raMin.resident=r)
                ) as admitted',
                'rr.id as rentId',
                'rr.amount as amount',
                'rr.period as period'
            );

        if ($reportInterval) {
            $qb
                ->andWhere('rr.end IS NULL OR rr.end > = :start');
            if ($reportInterval->getEnd()) {
                $qb
                    ->andWhere('rr.start < = :end');
            }
        }

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.shorthand as typeShorthand,
                        fr.number as roomNumber,
                        fr.floor as floor,
                        fb.number as bedNumber,
                        fb.id as bedId,
                        cl.title as careLevel'
                    )
                    ->innerJoin(
                        FacilityBed::class,
                        'fb',
                        Join::WITH,
                        'ra.facilityBed = fb'
                    )
                    ->innerJoin(
                        FacilityRoom::class,
                        'fr',
                        Join::WITH,
                        'fb.room = fr'
                    )
                    ->innerJoin(
                        Facility::class,
                        'f',
                        Join::WITH,
                        'fr.facility = f'
                    )
                    ->innerJoin(
                        CareLevel::class,
                        'cl',
                        Join::WITH,
                        'ra.careLevel = cl'
                    );

                $qb
                    ->orderBy('f.shorthand')
                    ->addOrderBy('fr.number')
                    ->addOrderBy('fb.number');

                if ($typeId) {
                    $qb
                        ->andWhere('f.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            case GroupType::TYPE_APARTMENT:
                $qb
                    ->addSelect(
                        'a.id as typeId,
                        a.name as typeName,
                        a.shorthand as typeShorthand,
                        ar.number as roomNumber,
                        ar.floor as floor,
                        ab.number as bedNumber
                        ab.id as bedId'
                    )
                    ->innerJoin(
                        ApartmentBed::class,
                        'ab',
                        Join::WITH,
                        'ra.apartmentBed = ab'
                    )
                    ->innerJoin(
                        ApartmentRoom::class,
                        'ar',
                        Join::WITH,
                        'ab.room = ar'
                    )
                    ->innerJoin(
                        Apartment::class,
                        'a',
                        Join::WITH,
                        'ar.apartment = a'
                    );

                $qb
                    ->orderBy('a.shorthand')
                    ->addOrderBy('ar.number')
                    ->addOrderBy('ab.number');

                if ($typeId) {
                    $qb
                        ->andWhere('a.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            case GroupType::TYPE_REGION:
                $qb
                    ->addSelect(
                        'reg.id as typeId,
                        reg.name as typeName,
                        reg.shorthand as typeShorthand,
                        ra.address as address,
                        csz.city as city,
                        csz.stateAbbr as state,
                        csz.zipMain as zip,
                        cl.title as careLevel'
                    )
                    ->innerJoin(
                        Region::class,
                        'reg',
                        Join::WITH,
                        'ra.region = reg'
                    )
                    ->innerJoin(
                        CareLevel::class,
                        'cl',
                        Join::WITH,
                        'ra.careLevel = cl'
                    )
                    ->innerJoin(
                        CityStateZip::class,
                        'csz',
                        Join::WITH,
                        'ra.csz = csz'
                    );

                $qb
                    ->orderBy('reg.shorthand');

                if ($typeId) {
                    $qb
                        ->andWhere('reg.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        return $qb;
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param ImtDateTimeInterval $reportInterval
     * @param null $typeId
     * @param array|null $notGrantResidentIds
     * @return mixed
     */
    public function getAdmissionRoomListData(Space $space = null, array $entityGrants = null, $type, ImtDateTimeInterval $reportInterval, $typeId = null, array $notGrantResidentIds = null)
    {
        $qb = $this
            ->getRoomListResidentAdmissionWithRentQb($type, $reportInterval, $typeId)
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) 
                        FROM App:ResidentRent mrr 
                        JOIN mrr.resident res 
                        WHERE (mrr.start < = :endDate AND mrr.start > = :startDate) OR (mrr.start < :startDate AND (mrr.end IS NULL OR mrr.end > :startDate))
                        GROUP BY res.id)'
            )
            ->andWhere('ra.id IN (SELECT MAX(mra.id)
                        FROM App:ResidentAdmission mra
                        JOIN mra.resident mrar
                        WHERE (mra.start < = :endDate AND mra.start > = :startDate) OR (mra.start < :startDate AND (mra.end IS NULL OR mra.end > :startDate))
                        GROUP BY mrar.id)'
            )
            ->andWhere('r.id IN (SELECT ar.id 
                        FROM App:ResidentAdmission ara 
                        JOIN ara.resident ar 
                        WHERE ara.admissionType<'. AdmissionType::DISCHARGE .' AND ara.end IS NULL)'
            )
            ->setParameter('startDate', $reportInterval->getStart())
            ->setParameter('endDate', $reportInterval->getEnd());

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rar.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($notGrantResidentIds !== null) {
            $qb
                ->andWhere('r.id NOT IN (:notGrantResidentIds)')
                ->setParameter('notGrantResidentIds', $notGrantResidentIds);
        }

        return $qb
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param null $typeId
     * @param array|null $notGrantResidentIds
     * @return mixed
     */
    public function getAdmissionRoomRentData(Space $space = null, array $entityGrants = null, $type, ImtDateTimeInterval $reportInterval = null, $typeId = null, array $notGrantResidentIds = null)
    {
        $qb = $this
            ->getResidentAdmissionWithRentQb($type, $reportInterval, $typeId)
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) 
                        FROM App:ResidentRent mrr 
                        JOIN mrr.resident res 
                        WHERE (mrr.end IS NULL OR mrr.end > = ra.start) AND (ra.end IS NULL OR mrr.start < = ra.end)
                        GROUP BY res.id)'
            )
            ->andWhere('r.id IN (SELECT ar.id 
                        FROM App:ResidentAdmission ara 
                        JOIN ara.resident ar 
                        WHERE ara.admissionType<'. AdmissionType::DISCHARGE .' AND ara.end IS NULL)'
            );

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rar.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($notGrantResidentIds !== null) {
            $qb
                ->andWhere('r.id NOT IN (:notGrantResidentIds)')
                ->setParameter('notGrantResidentIds', $notGrantResidentIds);
        }

        return $qb
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param null $typeId
     * @param array|null $notGrantResidentIds
     * @return mixed
     */
    public function getAdmissionRoomRentMasterData(Space $space = null, array $entityGrants = null, $type, ImtDateTimeInterval $reportInterval = null, $typeId = null, array $notGrantResidentIds = null)
    {
        $qb = $this
            ->getResidentAdmissionWithRentQb($type, $reportInterval, $typeId)
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) 
                        FROM App:ResidentRent mrr 
                        JOIN mrr.resident res 
                        WHERE (mrr.end IS NULL OR mrr.end > = ra.start) AND (ra.end IS NULL OR mrr.start < = ra.end)
                        GROUP BY res.id)'
            )
            ->andWhere('r.id IN (SELECT ar.id 
                        FROM App:ResidentAdmission ara 
                        JOIN ara.resident ar 
                        WHERE ara.admissionType<'. AdmissionType::DISCHARGE .' AND ara.end IS NULL)'
            );

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rar.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($notGrantResidentIds !== null) {
            $qb
                ->andWhere('r.id NOT IN (:notGrantResidentIds)')
                ->setParameter('notGrantResidentIds', $notGrantResidentIds);
        }

        return $qb
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param null $typeId
     * @param array|null $notGrantResidentIds
     * @return mixed
     */
    public function getAdmissionRoomRentMasterNewData(Space $space = null, array $entityGrants = null, $type, ImtDateTimeInterval $reportInterval = null, $typeId = null, array $notGrantResidentIds = null)
    {
        $qb = $this
            ->getResidentAdmissionWithRentQb($type, $reportInterval, $typeId)
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) 
                        FROM App:ResidentRent mrr 
                        JOIN mrr.resident res 
                        WHERE (mrr.end IS NULL OR mrr.end > = ra.start) AND (ra.end IS NULL OR mrr.start < = ra.end)
                        GROUP BY res.id)'
            )
            ->andWhere('r.id IN (SELECT ar.id 
                        FROM App:ResidentAdmission ara 
                        JOIN ara.resident ar 
                        WHERE ara.admissionType<'. AdmissionType::DISCHARGE .' AND ara.end IS NULL)'
            );

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rar.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($notGrantResidentIds !== null) {
            $qb
                ->andWhere('r.id NOT IN (:notGrantResidentIds)')
                ->setParameter('notGrantResidentIds', $notGrantResidentIds);
        }

        return $qb
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
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
            ->createQueryBuilder('rr')
            ->select('rr.amount');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('rr.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('rr.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('rr.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rr.resident'
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
                ->andWhere('rr.id IN (:grantIds)')
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
    public function getWithSources(Space $space = null, array $entityGrants = null)
    {
        $qb = $this
            ->createQueryBuilder('rr')
            ->select(
                'rr.amount',
                'rr.source'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rr.resident'
            );

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
                ->andWhere('rr.id IN (:grantIds)')
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
    public function getEntityWithSources(Space $space = null, array $entityGrants = null)
    {
        $qb = $this
            ->createQueryBuilder('rr')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rr.resident'
            );

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
                ->andWhere('rr.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}