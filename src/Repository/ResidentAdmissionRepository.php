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
                    JSON_OBJECT('Room (Bed)', CONCAT(fr.number, ' (', fb.number, ')')),
                    JSON_OBJECT('Dinning Room', dr.title),
                    
                    JSON_OBJECT('Apartment', a.name),
                    JSON_OBJECT('Room (Bed)', CONCAT(ar.number, ' (', ab.number, ')')),
                    
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
     * @return mixed
     */
    public function getActiveResidents(Space $space = null, array $entityGrants = null, $type, array $ids = null)
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
            ->addOrderBy("CONCAT(
            CASE WHEN rs IS NOT NULL THEN CONCAT(rs.title, ' ') ELSE '' END,
            r.firstName, ' ', r.lastName)", 'ASC');

        return $qb
            ->getQuery()
            ->getResult();
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
            ->addOrderBy("CONCAT(
            CASE WHEN rs IS NOT NULL THEN CONCAT(rs.title, ' ') ELSE '' END,
            r.firstName, ' ', r.lastName)", 'ASC');

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
}