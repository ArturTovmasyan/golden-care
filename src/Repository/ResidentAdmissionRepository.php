<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\CareLevel;
use App\Entity\CityStateZip;
use App\Entity\DiningRoom;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentAdmission;
use App\Entity\Space;
use App\Model\AdmissionType;
use App\Model\GroupType;
use App\Util\Common\ImtDateTimeInterval;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentAdmissionRepository
 * @package App\Repository
 */
class ResidentAdmissionRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(ResidentAdmission::class, 'ra')

            ->addSelect("
                JSON_ARRAY(
                    JSON_OBJECT('Facility', f.name),
                    JSON_OBJECT(
                        CASE
                            WHEN fr.private = 1 THEN 'Room'
                            WHEN fr.private = 0 THEN 'Room (Bed)'
                            ELSE 'Room (Bed)' END
                        , 
                        CASE
                            WHEN fr.private = 1 THEN fr.number
                            WHEN fr.private = 0 THEN CONCAT(fr.number, ' (', fb.number, ')')
                            ELSE CONCAT(fr.number, ' (', fb.number, ')') END
                    ),
                    JSON_OBJECT('Dining Room', dr.title),
                    
                    JSON_OBJECT('Apartment', a.name),
                    JSON_OBJECT(
                        CASE
                            WHEN ar.private = 1 THEN 'Room'
                            WHEN ar.private = 0 THEN 'Room (Bed)'
                            ELSE 'Room (Bed)' END
                        , 
                        CASE
                            WHEN ar.private = 1 THEN ar.number
                            WHEN ar.private = 0 THEN CONCAT(ar.number, ' (', ab.number, ')')
                            ELSE CONCAT(ar.number, ' (', ab.number, ')') END
                    ),
                    
                    JSON_OBJECT('Region', reg.name),
                    JSON_OBJECT('Address', ra.address),
                    JSON_OBJECT('CSZ', CONCAT(csz.city, ' ', csz.stateAbbr, ', ', csz.zipMain)),
                    
                    JSON_OBJECT('Care Group', ra.careGroup),
                    JSON_OBJECT('Care Level', cl.title),
                    JSON_OBJECT('Ambulatory', CAST(ra.ambulatory AS BOOLEAN)),
                    JSON_OBJECT('DNR', CAST(ra.dnr AS BOOLEAN)),
                    JSON_OBJECT('POLST', CAST(ra.polst AS BOOLEAN))
                ) as info
            ")

            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = ra.resident'
            )
            ->leftJoin(
                FacilityBed::class,
                'fb',
                Join::WITH,
                'fb = ra.facilityBed'
            )
            ->leftJoin(
                FacilityRoom::class,
                'fr',
                Join::WITH,
                'fr = fb.room'
            )
            ->leftJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = fr.facility'
            )
            ->leftJoin(
                ApartmentBed::class,
                'ab',
                Join::WITH,
                'ab = ra.apartmentBed'
            )
            ->leftJoin(
                ApartmentRoom::class,
                'ar',
                Join::WITH,
                'ar = ab.room'
            )
            ->leftJoin(
                Apartment::class,
                'a',
                Join::WITH,
                'a = ar.apartment'
            )
            ->leftJoin(
                Region::class,
                'reg',
                Join::WITH,
                'reg = ra.region'
            )
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'csz = ra.csz'
            )
            ->leftJoin(
                DiningRoom::class,
                'dr',
                Join::WITH,
                'dr = ra.diningRoom'
            )
            ->leftJoin(
                CareLevel::class,
                'cl',
                Join::WITH,
                'cl = ra.careLevel'
            )
            ->addOrderBy('ra.date');

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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->orderBy('ra.start', 'DESC')
            ->groupBy('ra.id');
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
            ->createQueryBuilder('ra')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = ra.resident'
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
                ->andWhere('ra.id IN (:grantIds)')
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
            ->createQueryBuilder('ra')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = ra.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('ra.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ra.id IN (:grantIds)')
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
            ->createQueryBuilder('ra')
            ->where('ra.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = ra.resident'
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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('ra.id')
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
            ->createQueryBuilder('ra')
            ->join('ra.resident', 'r')
            ->where('r.id=:id')
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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('ra.start', 'DESC')
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
    public function getOneAdmitAction(Space $space = null, array $entityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('ra')
            ->join('ra.resident', 'r')
            ->where('r.id=:id')
            ->andWhere('ra.admissionType=:admissionType')
            ->setParameter('id', $id)
            ->setParameter('admissionType', AdmissionType::ADMIT);

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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('ra.id', 'DESC')
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
    public function getByOrderedStartDate(Space $space = null, array $entityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('ra')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = ra.resident'
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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('ra.start', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @return mixed
     */
    public function getActiveByResident(Space $space = null, array $entityGrants = null, $id)
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->join('ra.resident', 'r')
            ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
            ->andWhere('r.id=:id')
            ->setParameter('id', $id)
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('ra.id', 'DESC')
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
    public function getInactiveByResident(Space $space = null, array $entityGrants = null, $id)
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->join('ra.resident', 'r')
            ->where('ra.admissionType = :admissionType AND ra.end IS NULL')
            ->andWhere('r.id=:id')
            ->setParameter('id', $id)
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('ra.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param $id
     * @return mixed
     */
    public function getDataByResident(Space $space = null, array $entityGrants = null, $type, $id)
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->join('ra.resident', 'r')
            ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
            ->andWhere('r.id=:id')
            ->andWhere('ra.groupType=:type')
            ->setParameter('id', $id)
            ->setParameter('type', $type)
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param array|null $ids
     * @return QueryBuilder
     */
    public function getActiveResidentsQb(Space $space = null, array $entityGrants = null, $type, array $ids = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->select(
                'r.id AS id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'r.middleName AS middle_name',
                'rs.title AS salutation'
            )
            ->join('ra.resident', 'r')
            ->leftJoin('r.salutation', 'rs')
            ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
            ->andWhere('ra.groupType=:type')
            ->setParameter('type', $type)
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'fbr.number AS room_number',
                        'fbr.private AS private',
                        'fb.number AS bed_number',
                        'fbrf.id AS type_id'
                    )
                    ->join('ra.facilityBed', 'fb')
                    ->join('fb.room', 'fbr')
                    ->join('fbr.facility', 'fbrf')
                    ->orderBy('fbr.number')
                    ->addOrderBy('fb.number');

                if ($ids !== null) {
                    $qb
                        ->andWhere('fbrf.id IN (:ids)')
                        ->setParameter('ids', $ids);
                }
                break;
            case GroupType::TYPE_APARTMENT:
                $qb
                    ->addSelect(
                        'abr.number AS room_number',
                        'abr.private AS private',
                        'ab.number AS bed_number',
                        'abra.id AS type_id'
                    )
                    ->join('ra.apartmentBed', 'ab')
                    ->join('ab.room', 'abr')
                    ->join('abr.apartment', 'abra')
                    ->orderBy('abr.number')
                    ->addOrderBy('ab.number');

                if ($ids !== null) {
                    $qb
                        ->andWhere('abra.id IN (:ids)')
                        ->setParameter('ids', $ids);
                }
                break;
            case GroupType::TYPE_REGION:
                $qb
                    ->addSelect(
                        'reg.id AS type_id'
                    )
                    ->join('ra.region', 'reg')
                    ->orderBy('reg.name');

                if ($ids !== null) {
                    $qb
                        ->andWhere('reg.id IN (:ids)')
                        ->setParameter('ids', $ids);
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
     * @param array|null $ids
     * @return mixed
     */
    public function getActiveResidents(Space $space = null, array $entityGrants = null, $type, array $ids = null)
    {
        $qb = $this->getActiveResidentsQb($space, $entityGrants, $type, $ids);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $notGrantResidentIds
     * @param $page
     * @param $perPage
     * @param $inactive
     * @param null $type
     * @param null $typeId
     * @return mixed
     */
    public function getPerPageActiveOrInactiveResidents(Space $space = null, array $entityGrants = null, array $notGrantResidentIds = null, $page, $perPage, $inactive, $type = null, $typeId = null)
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->select(
                'r.id AS id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'r.middleName AS middle_name',
                'rs.title AS salutation'
            )
            ->join('ra.resident', 'r')
            ->leftJoin('r.salutation', 'rs')
            ->leftJoin('ra.facilityBed', 'fb')
            ->leftJoin('fb.room', 'fr')
            ->leftJoin('fr.facility', 'f')
            ->leftJoin('ra.apartmentBed', 'ab')
            ->leftJoin('ab.room', 'ar')
            ->leftJoin('ar.apartment', 'a')
            ->leftJoin('ra.region', 'reg')
            ->leftJoin('ra.csz', 'csz');

        if ($inactive) {
            $qb
                ->where('ra.admissionType = :admissionType AND ra.end IS NULL')
                ->andWhere('r.id NOT IN (SELECT arar.id
                        FROM App:ResidentAdmission ara
                        JOIN ara.resident arar
                        WHERE ara.admissionType < :admissionType AND ara.end IS NULL)'
                )
                ->setParameter('admissionType', AdmissionType::DISCHARGE);
        } else {
            $qb
                ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
                ->setParameter('admissionType', AdmissionType::DISCHARGE);
        }

        $qb
            ->addSelect(
        '(CASE
                    WHEN fb.id IS NOT NULL THEN fb.number
                    WHEN ab.id IS NOT NULL THEN ab.number
                    ELSE \'\' END) as bed_number',
                '(CASE
                    WHEN fb.id IS NOT NULL THEN fr.number
                    WHEN ab.id IS NOT NULL THEN ar.number
                    ELSE \'\' END) as room_number',
                '(CASE
                    WHEN fb.id IS NOT NULL THEN fr.private
                    WHEN ab.id IS NOT NULL THEN ar.private
                    ELSE false END) as private',
                '(CASE
                    WHEN reg.id IS NOT NULL THEN ra.address
                    ELSE \'\' END) as address',
                '(CASE
                    WHEN reg.id IS NOT NULL THEN CONCAT(csz.city, \' \',csz.stateAbbr, \', \',csz.zipMain)
                    ELSE \'\' END) as csz_str',
                'CAST((CASE
                    WHEN fb.id IS NOT NULL THEN :facility_type
                    WHEN ab.id IS NOT NULL THEN :apartment_type
                    WHEN reg.id IS NOT NULL THEN :region_type
                    ELSE -1 END) AS INTEGER) as group_type'
            )
            ->setParameter('facility_type', GroupType::TYPE_FACILITY)
            ->setParameter('apartment_type', GroupType::TYPE_APARTMENT)
            ->setParameter('region_type', GroupType::TYPE_REGION);

        if ($type === null && $typeId === null) {
            $qb
                ->addSelect(
            '(CASE
                        WHEN fb.id IS NOT NULL THEN f.name
                        WHEN ab.id IS NOT NULL THEN a.name
                        WHEN reg.id IS NOT NULL THEN reg.name
                        ELSE \'\' END) as group_name'
                );
        } else {
            $qb
                ->addSelect(
                    '(CASE
                        WHEN fb.id IS NOT NULL THEN :groupName
                        WHEN ab.id IS NOT NULL THEN :groupName
                        WHEN reg.id IS NOT NULL THEN :groupName
                        ELSE :groupName END) as group_name'
                )
                ->andWhere('ra.groupType=:type')
                ->setParameter('type', $type)
                ->setParameter('groupName', null);

            switch ($type) {
                case GroupType::TYPE_FACILITY:
                    $qb
                        ->andWhere('f.id = :typeId')
                        ->setParameter('typeId', $typeId);
                    break;
                case GroupType::TYPE_APARTMENT:
                    $qb
                        ->andWhere('a.id = :typeId')
                        ->setParameter('typeId', $typeId);
                    break;
                case GroupType::TYPE_REGION:
                    $qb
                        ->andWhere('reg.id = :typeId')
                        ->setParameter('typeId', $typeId);
                    break;
                default:
                    throw new IncorrectStrategyTypeException();
            }
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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($notGrantResidentIds !== null) {
            $qb
                ->andWhere('r.id NOT IN (:notGrantResidentIds)')
                ->setParameter('notGrantResidentIds', $notGrantResidentIds);
        }

        $qb
            ->addOrderBy("CONCAT( r.lastName, ' ', r.firstName)", 'ASC');

        return $qb
            ->getQuery()
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $notGrantResidentIds
     * @param $page
     * @param $perPage
     * @param $inactive
     * @param null $type
     * @param null $typeId
     * @return mixed
     */
    public function getMobilePerPageActiveOrInactiveResidents(Space $space = null, array $entityGrants = null, array $notGrantResidentIds = null, $page, $perPage, $inactive, $type = null, $typeId = null)
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->select(
                'r.id AS id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'r.middleName AS middle_name',
                'r.birthday AS birthday',
                'r.gender AS gender',
                'r.ssn AS ssn',
                'rs.title AS salutation'
            )
            ->join('ra.resident', 'r')
            ->join('r.space', 's')
            ->leftJoin('r.salutation', 'rs')
            ->leftJoin('ra.facilityBed', 'fb')
            ->leftJoin('fb.room', 'fr')
            ->leftJoin('fr.facility', 'f')
            ->leftJoin('ra.apartmentBed', 'ab')
            ->leftJoin('ab.room', 'ar')
            ->leftJoin('ar.apartment', 'a')
            ->leftJoin('ra.region', 'reg')
            ->leftJoin('ra.csz', 'csz')
            ->leftJoin('ra.careLevel', 'cl')
            ->leftJoin('ra.diningRoom', 'dr');

        if ($inactive) {
            $qb
                ->where('ra.admissionType = :admissionType AND ra.end IS NULL')
                ->andWhere('r.id NOT IN (SELECT arar.id
                        FROM App:ResidentAdmission ara
                        JOIN ara.resident arar
                        WHERE ara.admissionType < :admissionType AND ara.end IS NULL)'
                )
                ->setParameter('admissionType', AdmissionType::DISCHARGE);
        } else {
            $qb
                ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
                ->setParameter('admissionType', AdmissionType::DISCHARGE);
        }

        $qb
            ->addSelect(
                'ra.admissionType as admission_type',
                'ra.date as effective_date',
                'ra.dnr as dnr',
                'ra.polst as polst',
                'ra.ambulatory as ambulatory',
                '(CASE
                    WHEN fb.id IS NOT NULL THEN fb.number
                    WHEN ab.id IS NOT NULL THEN ab.number
                    ELSE \'\' END) as bed_number',
                '(CASE
                    WHEN fb.id IS NOT NULL THEN fr.number
                    WHEN ab.id IS NOT NULL THEN ar.number
                    ELSE \'\' END) as room_number',
                '(CASE
                    WHEN fb.id IS NOT NULL THEN fr.private
                    WHEN ab.id IS NOT NULL THEN ar.private
                    ELSE false END) as private',
                '(CASE
                    WHEN reg.id IS NOT NULL THEN ra.address
                    ELSE \'\' END) as address',
                '(CASE
                    WHEN reg.id IS NOT NULL THEN CONCAT(csz.city, \' \',csz.stateAbbr, \', \',csz.zipMain)
                    ELSE \'\' END) as csz_str',
                '(CASE
                    WHEN fb.id IS NOT NULL THEN ra.careGroup
                    WHEN reg.id IS NOT NULL THEN ra.careGroup
                    ELSE :null_parameter END) as care_group',
                '(CASE
                    WHEN fb.id IS NOT NULL THEN cl.title
                    WHEN reg.id IS NOT NULL THEN cl.title
                    ELSE :null_parameter END) as care_level',
                '(CASE
                    WHEN dr.id IS NOT NULL THEN dr.title
                    ELSE :null_parameter END) as dinning_room',
                'CAST((CASE
                    WHEN fb.id IS NOT NULL THEN :facility_type
                    WHEN ab.id IS NOT NULL THEN :apartment_type
                    WHEN reg.id IS NOT NULL THEN :region_type
                    ELSE -1 END) AS INTEGER) as group_type'
            )
            ->setParameter('facility_type', GroupType::TYPE_FACILITY)
            ->setParameter('apartment_type', GroupType::TYPE_APARTMENT)
            ->setParameter('region_type', GroupType::TYPE_REGION)
            ->setParameter('null_parameter', null);

        if ($type === null && $typeId === null) {
            $qb
                ->addSelect(
                    '(CASE
                        WHEN fb.id IS NOT NULL THEN f.name
                        WHEN ab.id IS NOT NULL THEN a.name
                        WHEN reg.id IS NOT NULL THEN reg.name
                        ELSE \'\' END) as group_name'
                );
        } else {
            $qb
                ->addSelect(
                    '(CASE
                        WHEN fb.id IS NOT NULL THEN :groupName
                        WHEN ab.id IS NOT NULL THEN :groupName
                        WHEN reg.id IS NOT NULL THEN :groupName
                        ELSE :groupName END) as group_name'
                )
                ->andWhere('ra.groupType=:type')
                ->setParameter('type', $type)
                ->setParameter('groupName', null);

            switch ($type) {
                case GroupType::TYPE_FACILITY:
                    $qb
                        ->andWhere('f.id = :typeId')
                        ->setParameter('typeId', $typeId);
                    break;
                case GroupType::TYPE_APARTMENT:
                    $qb
                        ->andWhere('a.id = :typeId')
                        ->setParameter('typeId', $typeId);
                    break;
                case GroupType::TYPE_REGION:
                    $qb
                        ->andWhere('reg.id = :typeId')
                        ->setParameter('typeId', $typeId);
                    break;
                default:
                    throw new IncorrectStrategyTypeException();
            }
        }

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($notGrantResidentIds !== null) {
            $qb
                ->andWhere('r.id NOT IN (:notGrantResidentIds)')
                ->setParameter('notGrantResidentIds', $notGrantResidentIds);
        }

        $qb
            ->addOrderBy("CONCAT( r.lastName, ' ', r.firstName)", 'ASC');

        return $qb
            ->getQuery()
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $notGrantResidentIds
     * @param $inactive
     * @param null $type
     * @param null $typeId
     * @return mixed
     */
    public function getCountActiveOrInactiveResidents(Space $space = null, array $entityGrants = null, array $notGrantResidentIds = null, $inactive, $type = null, $typeId = null)
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->select('COUNT(r.id) AS total')
            ->join('ra.resident', 'r');

        if ($inactive) {
            $qb
                ->where('ra.admissionType = :admissionType AND ra.end IS NULL')
                ->andWhere('r.id NOT IN (SELECT ar.id
                        FROM App:ResidentAdmission ara
                        JOIN ara.resident ar
                        WHERE ara.admissionType < :admissionType AND ara.end IS NULL)'
                )
                ->setParameter('admissionType', AdmissionType::DISCHARGE);
        } else {
            $qb
                ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
                ->setParameter('admissionType', AdmissionType::DISCHARGE);
        }

        if ($type !== null && $typeId !== null) {
            $qb
                ->andWhere('ra.groupType=:type')
                ->setParameter('type', $type);

            switch ($type) {
                case GroupType::TYPE_FACILITY:
                    $qb
                        ->join('ra.facilityBed', 'fb')
                        ->join('fb.room', 'fr')
                        ->join('fr.facility', 'f')
                        ->andWhere('f.id = :typeId')
                        ->setParameter('typeId', $typeId);
                    break;
                case GroupType::TYPE_APARTMENT:
                    $qb
                        ->join('ra.apartmentBed', 'ab')
                        ->join('ab.room', 'ar')
                        ->join('ar.apartment', 'a')
                        ->andWhere('a.id = :typeId')
                        ->setParameter('typeId', $typeId);
                    break;
                case GroupType::TYPE_REGION:
                    $qb
                        ->join('ra.region', 'reg')
                        ->andWhere('reg.id = :typeId')
                        ->setParameter('typeId', $typeId);
                    break;
                default:
                    throw new IncorrectStrategyTypeException();
            }
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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($notGrantResidentIds !== null) {
            $qb
                ->andWhere('r.id NOT IN (:notGrantResidentIds)')
                ->setParameter('notGrantResidentIds', $notGrantResidentIds);
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param $id
     * @return mixed
     */
    public function getActiveResidentsByStrategy(Space $space = null, array $entityGrants = null, $type, $id)
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->select(
                'r.id AS id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'rs.title AS salutation'
            )
            ->join('ra.resident', 'r')
            ->leftJoin('r.salutation', 'rs')
            ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
            ->andWhere('ra.groupType=:type')
            ->setParameter('type', $type)
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'fbr.number AS room_number',
                        'fbr.private AS private',
                        'fb.number AS bed_number'
                    )
                    ->join('ra.facilityBed', 'fb')
                    ->join('fb.room', 'fbr')
                    ->join('fbr.facility', 'fbrf')
                    ->andWhere('fbrf.id=:id')
                    ->setParameter('id', $id)
                    ->orderBy('fbr.number')
                    ->addOrderBy('fb.number');
                break;
            case GroupType::TYPE_APARTMENT:
                $qb
                    ->addSelect(
                        'abr.number AS room_number',
                        'abr.private AS private',
                        'ab.number AS bed_number'
                    )
                    ->join('ra.apartmentBed', 'ab')
                    ->join('ab.room', 'abr')
                    ->join('abr.apartment', 'abra')
                    ->andWhere('abra.id=:id')
                    ->setParameter('id', $id)
                    ->orderBy('abr.number')
                    ->addOrderBy('ab.number');
                break;
            case GroupType::TYPE_REGION:
                $qb
                    ->join('ra.region', 'reg')
                    ->andWhere('reg.id=:id')
                    ->setParameter('id', $id)
                    ->orderBy('reg.name');
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        $qb
            ->addOrderBy("CONCAT( r.lastName, ' ', r.firstName)", 'ASC');

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param array|null $ids
     * @return QueryBuilder
     */
    public function getInactiveResidentsQb(Space $space = null, array $entityGrants = null, $type, array $ids = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->select(
                'r.id AS id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'r.middleName AS middle_name',
                'rs.title AS salutation'
            )
            ->join('ra.resident', 'r')
            ->leftJoin('r.salutation', 'rs')
            ->where('ra.admissionType = :admissionType AND ra.end IS NULL')
            ->andWhere('r.id NOT IN (SELECT ar.id
                        FROM App:ResidentAdmission ara
                        JOIN ara.resident ar
                        WHERE ara.admissionType < :admissionType AND ara.end IS NULL)'
            )
            ->andWhere('ra.groupType=:type')
            ->setParameter('type', $type)
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'fbr.number AS room_number',
                        'fbr.private AS private',
                        'fb.number AS bed_number',
                        'fbrf.id AS type_id'
                    )
                    ->join('ra.facilityBed', 'fb')
                    ->join('fb.room', 'fbr')
                    ->join('fbr.facility', 'fbrf')
                    ->orderBy('fbr.number')
                    ->addOrderBy('fb.number');

                if ($ids !== null) {
                    $qb
                        ->andWhere('fbrf.id IN (:ids)')
                        ->setParameter('ids', $ids);
                }
                break;
            case GroupType::TYPE_APARTMENT:
                $qb
                    ->addSelect(
                        'abr.number AS room_number',
                        'abr.private AS private',
                        'ab.number AS bed_number',
                        'abra.id AS type_id'
                    )
                    ->join('ra.apartmentBed', 'ab')
                    ->join('ab.room', 'abr')
                    ->join('abr.apartment', 'abra')
                    ->orderBy('abr.number')
                    ->addOrderBy('ab.number');

                if ($ids !== null) {
                    $qb
                        ->andWhere('abra.id IN (:ids)')
                        ->setParameter('ids', $ids);
                }
                break;
            case GroupType::TYPE_REGION:
                $qb
                    ->addSelect(
                        'reg.id AS type_id'
                    )
                    ->join('ra.region', 'reg')
                    ->orderBy('reg.name');

                if ($ids !== null) {
                    $qb
                        ->andWhere('reg.id IN (:ids)')
                        ->setParameter('ids', $ids);
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
     * @param array|null $ids
     * @return mixed
     */
    public function getInactiveResidents(Space $space = null, array $entityGrants = null, $type, array $ids = null)
    {
        $qb = $this->getInactiveResidentsQb($space, $entityGrants, $type, $ids);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param $id
     * @return mixed
     */
    public function getInactiveResidentsByStrategy(Space $space = null, array $entityGrants = null, $type, $id)
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->select(
                'ra.id AS raId',
                'r.id AS id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'rs.title AS salutation'
            )
            ->join('ra.resident', 'r')
            ->leftJoin('r.salutation', 'rs')
            ->where('ra.admissionType = :admissionType AND ra.end IS NULL')
            ->andWhere('ra.groupType=:type')
            ->andWhere('r.id NOT IN (SELECT ar.id
                        FROM App:ResidentAdmission ara
                        JOIN ara.resident ar
                        WHERE ara.admissionType < :admissionType AND ara.end IS NULL)'
            )
            ->setParameter('type', $type)
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'fbr.number AS room_number',
                        'fbr.private AS private',
                        'fb.number AS bed_number'
                    )
                    ->join('ra.facilityBed', 'fb')
                    ->join('fb.room', 'fbr')
                    ->join('fbr.facility', 'fbrf')
                    ->andWhere('fbrf.id=:id')
                    ->setParameter('id', $id)
                    ->orderBy('fbr.number')
                    ->addOrderBy('fb.number');
                break;
            case GroupType::TYPE_APARTMENT:
                $qb
                    ->addSelect(
                        'abr.number AS room_number',
                        'abr.private AS private',
                        'ab.number AS bed_number'
                    )
                    ->join('ra.apartmentBed', 'ab')
                    ->join('ab.room', 'abr')
                    ->join('abr.apartment', 'abra')
                    ->andWhere('abra.id=:id')
                    ->setParameter('id', $id)
                    ->orderBy('abr.number')
                    ->addOrderBy('ab.number');
                break;
            case GroupType::TYPE_REGION:
                $qb
                    ->join('ra.region', 'reg')
                    ->andWhere('reg.id=:id')
                    ->setParameter('id', $id)
                    ->orderBy('reg.name');
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        $qb
            ->addOrderBy("CONCAT( r.lastName, ' ', r.firstName)", 'ASC');

        return $qb
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param $ids
     * @return mixed
     */
    public function getBeds(Space $space = null, array $entityGrants = null, $type, $ids)
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->join('ra.resident', 'r')
            ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
            ->andWhere('ra.groupType=:type')
            ->setParameter('type', $type)
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->select('fb.id AS bedId')
                    ->join('ra.facilityBed', 'fb')
                    ->andWhere('fb.id IN (:ids)')
                    ->setParameter('ids', $ids);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            FacilityRoom::class,
                            'fr',
                            Join::WITH,
                            'fr = fb.room'
                        )
                        ->innerJoin(
                            Facility::class,
                            'f',
                            Join::WITH,
                            'f = fr.facility'
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
                break;
            case GroupType::TYPE_APARTMENT:
                $qb
                    ->select('ab.id AS bedId')
                    ->join('ra.apartmentBed', 'ab')
                    ->andWhere('ab.id IN (:ids)')
                    ->setParameter('ids', $ids);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            ApartmentRoom::class,
                            'ar',
                            Join::WITH,
                            'ar = ab.room'
                        )
                        ->innerJoin(
                            Apartment::class,
                            'a',
                            Join::WITH,
                            'a = ar.apartment'
                        )
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = a.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            case GroupType::TYPE_REGION:
                $qb
                    ->select('reg.id AS regionId')
                    ->join('ra.region', 'reg')
                    ->andWhere('reg.id IN (:ids)')
                    ->setParameter('ids', $ids);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = reg.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param $ids
     * @return mixed
     */
    public function getResidentsByBeds(Space $space = null, array $entityGrants = null, $type, $ids)
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->select(
                'ra AS admission',
                'r AS resident'
            )
            ->join('ra.resident', 'r')
            ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
            ->andWhere('ra.groupType=:type')
            ->setParameter('type', $type)
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect('fb.id AS bedId')
                    ->join('ra.facilityBed', 'fb')
                    ->andWhere('fb.id IN (:ids)')
                    ->setParameter('ids', $ids);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            FacilityRoom::class,
                            'fr',
                            Join::WITH,
                            'fr = fb.room'
                        )
                        ->innerJoin(
                            Facility::class,
                            'f',
                            Join::WITH,
                            'f = fr.facility'
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
                break;
            case GroupType::TYPE_APARTMENT:
                $qb
                    ->addSelect('ab.id AS bedId')
                    ->join('ra.apartmentBed', 'ab')
                    ->andWhere('ab.id IN (:ids)')
                    ->setParameter('ids', $ids);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            ApartmentRoom::class,
                            'ar',
                            Join::WITH,
                            'ar = ab.room'
                        )
                        ->innerJoin(
                            Apartment::class,
                            'a',
                            Join::WITH,
                            'a = ar.apartment'
                        )
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = a.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            case GroupType::TYPE_REGION:
                $qb
                    ->addSelect('reg.id AS regionId')
                    ->join('ra.region', 'reg')
                    ->andWhere('reg.id IN (:ids)')
                    ->setParameter('ids', $ids);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = reg.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param $id
     * @return mixed
     */
    public function getResidentByBed(Space $space = null, array $entityGrants = null, $type, $id)
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->select('r.id AS residentId')
            ->join('ra.resident', 'r')
            ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
            ->andWhere('ra.groupType=:type')
            ->setParameter('type', $type)
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->join('ra.facilityBed', 'fb')
                    ->andWhere('fb.id=:id')
                    ->setParameter('id', $id);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            FacilityRoom::class,
                            'fr',
                            Join::WITH,
                            'fr = fb.room'
                        )
                        ->innerJoin(
                            Facility::class,
                            'f',
                            Join::WITH,
                            'f = fr.facility'
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
                break;
            case GroupType::TYPE_APARTMENT:
                $qb
                    ->join('ra.apartmentBed', 'ab')
                    ->andWhere('ab.id=:id')
                    ->setParameter('id', $id);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            ApartmentRoom::class,
                            'ar',
                            Join::WITH,
                            'ar = ab.room'
                        )
                        ->innerJoin(
                            Apartment::class,
                            'a',
                            Join::WITH,
                            'a = ar.apartment'
                        )
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = a.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            case GroupType::TYPE_REGION:
                $qb
                    ->join('ra.region', 'reg')
                    ->andWhere('reg.id=:id')
                    ->setParameter('id', $id);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = reg.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param ImtDateTimeInterval|null $dateTimeInterval
     * @return QueryBuilder
     */
    public function getResidentAdmissionIntervalQb(ImtDateTimeInterval $dateTimeInterval = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ra');
        if ($dateTimeInterval) {
            $qb
                ->join('ra.resident', 'rar')
                ->where('ra.end IS NULL OR ra.end > = :start')
                ->setParameter('start', $dateTimeInterval->getStart());
            if ($dateTimeInterval->getEnd()) {
                $qb
                    ->andWhere('ra.start < = :end')
                    ->setParameter('end', $dateTimeInterval->getEnd());
            }
        }
        return $qb;
    }

    /**
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param null $typeId
     * @return QueryBuilder
     */
    public function getResidentAdmissionReportQb($type, ImtDateTimeInterval $reportInterval = null, $typeId = null) : QueryBuilder
    {
        /** @var ResidentAdmissionRepository $admissionRepo */
        $admissionRepo = $this
            ->getEntityManager()
            ->getRepository(ResidentAdmission::class);

        /** @var QueryBuilder $qb */
        $qb = $admissionRepo
            ->getResidentAdmissionIntervalQb($reportInterval);

        $qb
            ->from(Resident::class, 'r')
            ->andWhere('r.id = rar.id')
            ->andWhere('ra.groupType=:type')
            ->setParameter('type', $type)
            ->select(
                'r.id as id',
                'r.firstName as firstName',
                'r.lastName as lastName',
                'ra.id as actionId',
                'ra.admissionType as admissionType',
                'ra.notes as notes',
                'ra.start as admitted',
                'ra.end as discharged'
            );

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.shorthand as typeShorthand,
                        fr.number as roomNumber,
                        fr.private as private,
                        fb.number as bedNumber,
                        fb.id as bedId,
                        ra.careGroup as careGroup,
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
                        ar.private as private,
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
                        ra.careGroup as careGroup,
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
    public function getResidents60DaysRosterData(Space $space = null, array $entityGrants = null, $type, ImtDateTimeInterval $reportInterval = null, $typeId = null, array $notGrantResidentIds = null)
    {
        $qb = $this
            ->getResidentAdmissionReportQb($type, $reportInterval, $typeId)
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
    public function getRoomOccupancyRateByMonthData(Space $space = null, array $entityGrants = null, $type, ImtDateTimeInterval $reportInterval = null, $typeId = null, array $notGrantResidentIds = null)
    {
        $qb = $this
            ->getResidentAdmissionReportQb($type, $reportInterval, $typeId)
            ->andWhere('ra.admissionType !='. AdmissionType::DISCHARGE)
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
     * @param ImtDateTimeInterval|null $dateTimeInterval
     * @return QueryBuilder
     */
    public function getResidentAdmissionMoveByMonthIntervalQb(ImtDateTimeInterval $dateTimeInterval = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ra');
        if ($dateTimeInterval) {
            $qb
                ->join('ra.resident', 'rar')
                ->where('ra.end IS NULL OR (ra.end > = :start AND ra.end < = :end)')
                ->andWhere('ra.start < = :end AND ra.start > = :start')
                ->setParameter('start', $dateTimeInterval->getStart())
                ->setParameter('end', $dateTimeInterval->getEnd());
        }
        return $qb;
    }

    /**
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param null $typeId
     * @param bool $filter
     * @return QueryBuilder
     */
    public function getResidentAdmissionMoveByMonthReportQb($type, ImtDateTimeInterval $reportInterval = null, $typeId = null, $filter = false) : QueryBuilder
    {
        /** @var ResidentAdmissionRepository $admissionRepo */
        $admissionRepo = $this
            ->getEntityManager()
            ->getRepository(ResidentAdmission::class);

        if ($filter) {
            /** @var QueryBuilder $qb */
            $qb = $admissionRepo
                ->getResidentAdmissionMoveByMonthIntervalQb($reportInterval);
        } else {
            /** @var QueryBuilder $qb */
            $qb = $admissionRepo
                ->getResidentAdmissionIntervalQb($reportInterval);
        }

        $qb
            ->from(Resident::class, 'r')
            ->andWhere('r.id = rar.id')
            ->andWhere('ra.groupType=:type')
            ->setParameter('type', $type)
            ->select(
                'r.id as id',
                'r.firstName as firstName',
                'r.lastName as lastName',
                'ra.id as actionId',
                'ra.admissionType as admissionType',
                'ra.notes as notes',
                'ra.start as admitted',
                'ra.end as discharged'
            );

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.shorthand as typeShorthand,
                        fr.number as roomNumber,
                        fr.private as private,
                        fb.number as bedNumber,
                        fb.id as bedId,
                        ra.careGroup as careGroup,
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
                    ->addOrderBy('r.id')
                    ->addOrderBy('ra.start');

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
                        ar.private as private,
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
                    ->addOrderBy('r.id')
                    ->addOrderBy('ra.start');

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
                        ra.careGroup as careGroup,
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
                    );

                $qb
                    ->orderBy('reg.shorthand')
                    ->addOrderBy('r.id')
                    ->addOrderBy('ra.start');

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
     * @param null $dischargedAdmissionEnds
     * @param bool $filter
     * @return mixed
     */
    public function getResidentMoveByMonthData(Space $space = null, array $entityGrants = null, $type, ImtDateTimeInterval $reportInterval = null, $typeId = null, array $notGrantResidentIds = null, $dischargedAdmissionEnds = null, $filter = false)
    {
        $qb = $this
            ->getResidentAdmissionMoveByMonthReportQb($type, $reportInterval, $typeId, $filter);

        if ($dischargedAdmissionEnds !== null) {
            $qb
                ->andWhere('ra.admissionType ='. AdmissionType::ADMIT.' OR ra.admissionType='. AdmissionType::DISCHARGE. ' OR ra.start IN (:dischargedAdmissionEnds)')
                ->setParameter('dischargedAdmissionEnds', $dischargedAdmissionEnds);
        }

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
     * @param ImtDateTimeInterval $dateTimeInterval
     * @return QueryBuilder
     */
    public function getRoomListResidentAdmissionIntervalQb(ImtDateTimeInterval $dateTimeInterval): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ra');
        if ($dateTimeInterval) {
            $qb
                ->join('ra.resident', 'rar')
                ->andWhere('(ra.start < = :end AND ra.start > = :start) OR (ra.start < :start AND (ra.end IS NULL OR ra.end > :start))')
                ->setParameter('start', $dateTimeInterval->getStart())
                ->setParameter('end', $dateTimeInterval->getEnd());
        }
        return $qb;
    }

    public function getBedIdAndTypeId(Space $space = null, array $entityGrants = null, $type, $ids)
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->join('ra.resident', 'r')
            ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
            ->andWhere('ra.groupType=:type')
            ->setParameter('type', $type)
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->select('
                        fb.id AS bedId,
                        type.id AS typeId
                    ')
                    ->join('ra.facilityBed', 'fb')
                    ->join('fb.room', 'room')
                    ->join('room.facility', 'type')
                    ->andWhere('fb.id IN (:ids)')
                    ->setParameter('ids', $ids);
                break;
            case GroupType::TYPE_APARTMENT:
                $qb
                    ->select('
                        ab.id AS bedId,
                        type.id AS typeId
                    ')
                    ->join('ra.apartmentBed', 'ab')
                    ->join('ab.room', 'room')
                    ->join('room.apartment', 'type')
                    ->andWhere('ab.id IN (:ids)')
                    ->setParameter('ids', $ids);
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        return $qb
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
            ->createQueryBuilder('ra')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = ra.resident'
            )
            ->select("CONCAT(r.firstName, ' ', r.lastName) as fullName");

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('ra.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('ra.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('ra.id IN (:array)')
                ->setParameter('array', []);
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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array $residentIds
     * @param $type
     * @return mixed
     */
    public function getByResidentIds(Space $space = null, array $entityGrants = null, array $residentIds, $type)
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->select(
                'r.id as residentId',
                'ra.id as id',
                'ra.admissionType as admissionType',
                'ra.notes as notes',
                'ra.start as admitted',
                'ra.end as discharged'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'ra.resident = r'
            )
            ->where('r.id IN (:residentIds)')
            ->setParameter('residentIds', $residentIds);

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.shorthand as typeShorthand,
                        fr.number as roomNumber,
                        fr.private as private,
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

                break;
            case GroupType::TYPE_APARTMENT:
                $qb
                    ->addSelect(
                        'a.id as typeId,
                        a.name as typeName,
                        a.shorthand as typeShorthand,
                        ar.number as roomNumber,
                        ar.private as private,
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

                break;
            default:
                throw new IncorrectStrategyTypeException();
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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('ra.start', 'DESC')
            ->groupBy('ra.id')
            ->getQuery()
            ->getResult();
    }
}