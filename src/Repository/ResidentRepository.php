<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\IncorrectResidentStateException;
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
use App\Entity\Salutation;
use App\Entity\Space;
use App\Model\AdmissionType;
use App\Model\GroupType;
use App\Model\ResidentState;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentRepository
 * @package App\Repository
 */
class ResidentRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     * @param array|null $ids
     * @param array|null $notGrantResidentIds
     * @param $state
     * @param null $type
     * @param null $typeId
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder, array $ids = null, array $notGrantResidentIds = null, $state, $type = null, $typeId = null) : void
    {
        $queryBuilder
            ->from(Resident::class, 'r');

            switch ($state) {
                case ResidentState::TYPE_NO_ADMISSION:
                    break;
                case ResidentState::TYPE_ACTIVE || ResidentState::TYPE_INACTIVE:
                    $queryBuilder
                        ->addSelect(
                    '(CASE
                                WHEN fb.id IS NOT NULL AND fr.private = 1 THEN fr.number
                                WHEN fb.id IS NOT NULL AND fr.private = 0 THEN CONCAT(fr.number, \' (\',fb.number, \')\')
                                WHEN ab.id IS NOT NULL AND ar.private = 1 THEN ar.number
                                WHEN ab.id IS NOT NULL AND ar.private = 0 THEN CONCAT(ar.number, \' (\',ab.number, \')\')
                                ELSE \'\' END) as room',
                            '(CASE
                                WHEN reg.id IS NOT NULL THEN ra.address
                                ELSE \'\' END) as address',
                            '(CASE
                                WHEN reg.id IS NOT NULL THEN CONCAT(csz.city, \' \',csz.stateAbbr, \', \',csz.zipMain)
                                ELSE \'\' END) as csz_str'
                        )
                        ->innerJoin(
                            ResidentAdmission::class,
                            'ra',
                            Join::WITH,
                            'ra.resident = r'
                        )
                        ->leftJoin(
                            FacilityBed::class,
                            'fb',
                            Join::WITH,
                            'ra.facilityBed = fb'
                        )
                        ->leftJoin(
                            FacilityRoom::class,
                            'fr',
                            Join::WITH,
                            'fb.room = fr'
                        )
                        ->leftJoin(
                            Facility::class,
                            'f',
                            Join::WITH,
                            'fr.facility = f'
                        )
                        ->leftJoin(
                            ApartmentBed::class,
                            'ab',
                            Join::WITH,
                            'ra.apartmentBed = ab'
                        )
                        ->leftJoin(
                            ApartmentRoom::class,
                            'ar',
                            Join::WITH,
                            'ab.room = ar'
                        )
                        ->leftJoin(
                            Apartment::class,
                            'a',
                            Join::WITH,
                            'ar.apartment = a'
                        )
                        ->leftJoin(
                            Region::class,
                            'reg',
                            Join::WITH,
                            'ra.region = reg'
                        )
                        ->leftJoin(
                            CityStateZip::class,
                            'csz',
                            Join::WITH,
                            'ra.csz = csz'
                        );

                    if ($state === ResidentState::TYPE_ACTIVE) {
                        $queryBuilder
                            ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
                            ->setParameter('admissionType', AdmissionType::DISCHARGE);
                    }

                    if ($state === ResidentState::TYPE_INACTIVE) {
                        $queryBuilder
                            ->where('ra.admissionType = :admissionType AND ra.end IS NULL')
                            ->setParameter('admissionType', AdmissionType::DISCHARGE);
                    }

                    if ($type === null && $typeId === null) {
                        $queryBuilder
                            ->addSelect(
                        '(CASE
                                    WHEN fb.id IS NOT NULL THEN f.name
                                    WHEN ab.id IS NOT NULL THEN a.name
                                    WHEN reg.id IS NOT NULL THEN reg.name
                                    ELSE \'\' END) as group_name'
                            );
                    } else {
                        $queryBuilder
                            ->andWhere('ra.groupType=:type')
                            ->setParameter('type', $type);

                        switch ($type) {
                            case GroupType::TYPE_FACILITY:
                                $queryBuilder
                                    ->andWhere('f.id = :typeId')
                                    ->setParameter('typeId', $typeId);
                                break;
                            case GroupType::TYPE_APARTMENT:
                                $queryBuilder
                                    ->andWhere('a.id = :typeId')
                                    ->setParameter('typeId', $typeId);
                                break;
                            case GroupType::TYPE_REGION:
                                $queryBuilder
                                    ->andWhere('reg.id = :typeId')
                                    ->setParameter('typeId', $typeId);
                                break;
                            default:
                                throw new IncorrectStrategyTypeException();
                        }
                    }
                    break;
                default:
                    throw new IncorrectResidentStateException();
            }

        $queryBuilder
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->innerJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'sal = r.salutation'
            );

        if ($space !== null) {
            $queryBuilder
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($ids !== null) {
            $queryBuilder
                ->andWhere('r.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($notGrantResidentIds !== null) {
            $queryBuilder
                ->andWhere('r.id NOT IN (:notGrantResidentIds)')
                ->setParameter('notGrantResidentIds', $notGrantResidentIds);
        }

        $queryBuilder
            ->addOrderBy("CONCAT( r.lastName, ' ', r.firstName)", 'ASC');

        $queryBuilder
            ->groupBy('r.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $ids
     * @param array|null $notGrantResidentIds
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null, array $ids = null, array $notGrantResidentIds = null)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->leftJoin(
                Salutation::class,
                'rs',
                Join::WITH,
                'rs = r.salutation'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            );

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('r.id IN (:ids)')
                ->setParameter('ids', $ids);
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
            ->createQueryBuilder('r')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('r.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
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
            ->createQueryBuilder('r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids);

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
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return QueryBuilder
     */
    public function getNoAdmissionResidentsQb(Space $space = null, array $entityGrants = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r');

        $qb
            ->select(
                'r.id AS id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'r.middleName AS middle_name',
                'rs.title AS salutation'
            )
            ->innerJoin('r.salutation', 'rs')
            ->where('r.id NOT IN (SELECT ar.id FROM App:ResidentAdmission ra JOIN ra.resident ar)');

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
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $qb
            ->addOrderBy("CONCAT( r.lastName, ' ', r.firstName)", 'ASC');

        return $qb;
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return mixed
     */
    public function getNoAdmissionResidents(Space $space = null, array $entityGrants = null)
    {
        $qb = $this->getNoAdmissionResidentsQb($space, $entityGrants);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $page
     * @param $perPage
     * @return mixed
     */
    public function getPerPageNoAdmissionResidents(Space $space = null, array $entityGrants = null, $page, $perPage)
    {
        $qb = $this->getNoAdmissionResidentsQb($space, $entityGrants);

        return $qb
            ->getQuery()
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $page
     * @param $perPage
     * @param $date
     * @return mixed
     */
    public function getMobilePerPageNoAdmissionResidents(Space $space = null, array $entityGrants = null, $page, $perPage, $date)
    {
        $qb = $this->createQueryBuilder('r');

        $qb
            ->select(
                'r.id AS id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'r.middleName AS middle_name',
                'r.birthday AS birthday',
                'r.gender AS gender',
                'r.ssn AS ssn',
                'r.updatedAt AS updated_at',
                'rs.title AS salutation',
                's.name AS space'
            )
            ->innerJoin('r.salutation', 'rs')
            ->innerJoin('r.space', 's')
            ->where('r.id NOT IN (SELECT ar.id FROM App:ResidentAdmission ra JOIN ra.resident ar)')
            ->andWhere('r.updatedAt > :date')
            ->setParameter('date', $date);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
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
     * @param null $date
     * @return mixed
     */
    public function getCountNoAdmissionResidents(Space $space = null, array $entityGrants = null, $date = null)
    {
        $qb = $this->createQueryBuilder('r');

        $qb
            ->select('COUNT(r.id) AS total')
            ->where('r.id NOT IN (SELECT ar.id FROM App:ResidentAdmission ra JOIN ra.resident ar)');

        if ($date !== null) {
            $qb
                ->andWhere('r.updatedAt > :date')
                ->setParameter('date', $date);
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
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @return mixed
     */
    public function getNoAdmissionResidentById(Space $space = null, array $entityGrants = null, $id)
    {
        $qb = $this->createQueryBuilder('r');

        $qb
            ->select(
                'r.id AS id',
                'r.firstName AS firstName',
                'r.lastName AS lastName',
                'r.birthday AS birthday',
                'r.gender AS gender',
                'rs.title AS salutation'
            )
            ->innerJoin('r.salutation', 'rs')
            ->where('r.id NOT IN (SELECT ar.id FROM App:ResidentAdmission ra JOIN ra.resident ar)')
            ->andWhere('r.id = :id')
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
                ->andWhere('r.id IN (:grantIds)')
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
     * @return null
     */
    public function getResidentStateById(Space $space = null, array $entityGrants = null, $id)
    {
        $state = null;

        /** @var ResidentAdmissionRepository $admissionRepo */
        $admissionRepo = $this->_em->getRepository(ResidentAdmission::class);

        $activeResident = $admissionRepo->getActiveByResident($space, $entityGrants, $id);

        if ($activeResident !== null) {
            $state = ResidentState::TYPE_ACTIVE;
        }

        $inActiveResident = $admissionRepo->getInactiveByResident($space, $entityGrants, $id);

        if ($inActiveResident !== null) {
            $state = ResidentState::TYPE_INACTIVE;
        }

        $noAdmissionResident = $this->getNoAdmissionResidentById($space, $entityGrants, $id);

        if (!empty($noAdmissionResident)) {
            $state = ResidentState::TYPE_NO_ADMISSION;
        }

        return $state;
    }

    ////////////////////////////Resident Admission Part///////////////////////////////////////////////////
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param null $typeId
     * @param null $residentId
     * @param array|null $notGrantResidentIds
     * @return mixed
     */
    public function getAdmissionResidentsInfoByTypeOrId(Space $space = null, array $entityGrants = null, $type, $typeId = null, $residentId = null, array $notGrantResidentIds = null)
    {
        /**
         * @var ResidentAdmission $admission
         */
        $qb = $this->createQueryBuilder('r');

        $qb
            ->select(
                'r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    ra.groupType as type,
                    ra.dnr as dnr,
                    ra.ambulatory as ambulatory,
                    r.birthday as birthday,
                    sal.title as salutation'
            )
            ->innerJoin(
                ResidentAdmission::class,
                'ra',
                Join::WITH,
                'ra.resident = r'
            )
            ->leftJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'r.salutation = sal'
            )
            ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
            ->andWhere('ra.groupType=:type')
            ->setParameter('type', $type)
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.shorthand as typeShorthand,
                        f.address as address,
                        f.license as license,
                        fr.number as roomNumber,
                        fr.private as private,
                        fr.floor as floor,
                        fb.number as bedNumber,
                        f.numberOfFloors as numberOfFloors'
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
                    ->orderBy('f.name')
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
                        a.address as address,
                        a.license as license,
                        ar.number as roomNumber,
                        ar.private as private,
                        ar.floor as floor,
                        ab.number as bedNumber'
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
                    ->orderBy('a.name')
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
                        'ra.address as address,
                        csz.city as city,
                        csz.stateAbbr as state,
                        csz.zipMain as zip,
                        reg.id as typeId,
                        reg.shorthand as typeShorthand,
                        reg.name as typeName'
                    )
                    ->innerJoin(
                        Region::class,
                        'reg',
                        Join::WITH,
                        'ra.region = reg'
                    )
                    ->innerJoin(
                        CityStateZip::class,
                        'csz',
                        Join::WITH,
                        'ra.csz = csz'
                    );

                $qb
                    ->orderBy('reg.name');

                if ($typeId) {
                    $qb
                        ->andWhere('reg.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
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
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($notGrantResidentIds !== null) {
            $qb
                ->andWhere('r.id NOT IN (:notGrantResidentIds)')
                ->setParameter('notGrantResidentIds', $notGrantResidentIds);
        }

        if ($residentId) {
            $qb
                ->andWhere('r.id = :id')
                ->setParameter('id', $residentId);
        }

        return $qb
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param null $typeId
     * @param null $residentId
     * @param array|null $notGrantResidentIds
     * @return mixed
     */
    public function getAdmissionResidentsInfoWithCareGroupByTypeOrId(Space $space = null, array $entityGrants = null, $type, $typeId = null, $residentId = null, array $notGrantResidentIds = null)
    {
        /**
         * @var ResidentAdmission $admission
         */
        $qb = $this->createQueryBuilder('r');

        $qb
            ->select(
                'r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    ra.groupType as type,
                    ra.dnr as dnr,
                    r.birthday as birthday,
                    sal.title as salutation'
            )
            ->innerJoin(
                ResidentAdmission::class,
                'ra',
                Join::WITH,
                'ra.resident = r'
            )
            ->leftJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'r.salutation = sal'
            )
            ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
            ->andWhere('ra.groupType=:type')
            ->setParameter('type', $type)
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.address as address,
                        f.license as license,
                        fr.number as roomNumber,
                        fr.private as private,
                        fr.floor as floor,
                        fb.number as bedNumber,
                        ra.careGroup as careGroup'
                    )
                    ->innerJoin(
                        DiningRoom::class,
                        'dr',
                        Join::WITH,
                        'ra.diningRoom = dr'
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
                    ->orderBy('f.name')
                    ->addOrderBy('ra.careGroup')
                    ->addOrderBy('fr.number')
                    ->addOrderBy('fb.number');

                if ($typeId) {
                    $qb
                        ->andWhere('f.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            case GroupType::TYPE_REGION:
                $qb
                    ->addSelect(
                        'ra.address as address,
                        csz.city as city,
                        csz.stateAbbr as state,
                        csz.zipMain as zip,
                        reg.id as typeId,
                        reg.name as typeName,
                        ra.careGroup as careGroup'
                    )
                    ->innerJoin(
                        Region::class,
                        'reg',
                        Join::WITH,
                        'ra.region = reg'
                    )
                    ->innerJoin(
                        CityStateZip::class,
                        'csz',
                        Join::WITH,
                        'ra.csz = csz'
                    );

                $qb
                    ->orderBy('reg.name')
                    ->addOrderBy('ra.careGroup');

                if ($typeId) {
                    $qb
                        ->andWhere('reg.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
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
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($notGrantResidentIds !== null) {
            $qb
                ->andWhere('r.id NOT IN (:notGrantResidentIds)')
                ->setParameter('notGrantResidentIds', $notGrantResidentIds);
        }

        if ($residentId) {
            $qb
                ->andWhere('r.id = :id')
                ->setParameter('id', $residentId);
        }

        return $qb
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param null $typeId
     * @param null $residentId
     * @param array|null $notGrantResidentIds
     * @return mixed
     */
    public function getAdmissionDietaryRestrictionsInfo(Space $space = null, array $entityGrants = null, $type, $typeId = null, $residentId = null, array $notGrantResidentIds = null)
    {
        /**
         * @var ResidentAdmission $admission
         */
        $qb = $this->createQueryBuilder('r');

        $qb
            ->select(
                'r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,        
                    ra.groupType as type,
                    (SELECT DISTINCT mra.start FROM App:ResidentAdmission mra
                    WHERE mra.resident=r
                    AND mra.start = (SELECT MIN(raMin.start) FROM App:ResidentAdmission raMin WHERE raMin.resident=r) 
                    )
                    as startDate,
                    ra.admissionType as state,
                    ra.dnr as dnr,
                    ra.polst as polst,
                    ra.ambulatory as ambulatory,
                    ra.careGroup as careGroup,
                    cl.title as careLevel,
                    r.birthday as birthday,
                    r.gender as gender,
                    sal.title as salutation'
            )
            ->innerJoin(
                ResidentAdmission::class,
                'ra',
                Join::WITH,
                'ra.resident = r'
            )
            ->leftJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'r.salutation = sal'
            )
            ->innerJoin(
                CareLevel::class,
                'cl',
                Join::WITH,
                'ra.careLevel = cl'
            )
            ->where('ra.admissionType < :admissionType AND ra.end IS NULL')
            ->andWhere('ra.groupType=:type')
            ->setParameter('type', $type)
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.address as address,
                        f.license as license,
                        f.phone as typePhone,
                        f.fax as typeFax,
                        fr.number as roomNumber,
                        fr.private as private,
                        fr.floor as floor,
                        fb.number as bedNumber,
                        dr.title as diningRoom'
                    )
                    ->innerJoin(
                        DiningRoom::class,
                        'dr',
                        Join::WITH,
                        'ra.diningRoom = dr'
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
                    ->orderBy('f.name')
                    ->addOrderBy('dr.title')
                    ->addOrderBy('fr.number')
                    ->addOrderBy('fb.number');

                if ($typeId) {
                    $qb
                        ->andWhere('f.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            case GroupType::TYPE_REGION:
                $qb
                    ->addSelect(
                        'ra.address as address,
                        csz.city as city,
                        csz.stateAbbr as state,
                        csz.zipMain as zip,
                        reg.id as typeId,
                        reg.name as typeName,
                        reg.phone as typePhone,
                        reg.fax as typeFax'
                    )
                    ->innerJoin(
                        Region::class,
                        'reg',
                        Join::WITH,
                        'ra.region = reg'
                    )
                    ->innerJoin(
                        CityStateZip::class,
                        'csz',
                        Join::WITH,
                        'ra.csz = csz'
                    );

                $qb
                    ->orderBy('reg.name');

                if ($typeId) {
                    $qb
                        ->andWhere('reg.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
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
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($notGrantResidentIds !== null) {
            $qb
                ->andWhere('r.id NOT IN (:notGrantResidentIds)')
                ->setParameter('notGrantResidentIds', $notGrantResidentIds);
        }

        if ($residentId) {
            $qb
                ->andWhere('r.id = :id')
                ->setParameter('id', $residentId);
        }

        return $qb
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param null $typeId
     * @param null $residentId
     * @param array|null $notGrantResidentIds
     * @return mixed
     */
    public function getAdmissionResidentsFullInfoByTypeOrId(Space $space = null, array $entityGrants = null, $type, $typeId = null, $residentId = null, array $notGrantResidentIds = null)
    {
        /**
         * @var ResidentAdmission $admission
         */
        $qb = $this->createQueryBuilder('r');

        $active = true;
        if ($residentId) {
            /** @var ResidentAdmissionRepository $admissionRepo */
            $admissionRepo = $this->_em->getRepository(ResidentAdmission::class);

            $admission = $admissionRepo->getActiveByResident($space, $entityGrants, $residentId);

            if ($admission === null) {
                $active = false;
            }
        }

        $qb
            ->select(
                'r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    ra.groupType as type,
                    (SELECT DISTINCT mra.start FROM App:ResidentAdmission mra
                    WHERE mra.resident=r
                    AND mra.start = (SELECT MIN(raMin.start) FROM App:ResidentAdmission raMin WHERE raMin.resident=r) 
                    )
                    as admitted,
                    ra.id as actionId,
                    ra.admissionType as state,
                    r.birthday as birthday,
                    r.gender as gender,
                    r.ssn as ssn,
                    sal.title as salutation'
            )
            ->innerJoin(
                ResidentAdmission::class,
                'ra',
                Join::WITH,
                'ra.resident = r'
            )
            ->leftJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'r.salutation = sal'
            )
            ->where('ra.groupType=:type')
            ->setParameter('type', $type);

        if ($active) {
            $qb
                ->andWhere('ra.admissionType < :admissionType AND ra.end IS NULL')
                ->setParameter('admissionType', AdmissionType::DISCHARGE);
        } else {
            $qb
                ->andWhere('ra.admissionType = :admissionType AND ra.end IS NULL')
                ->setParameter('admissionType', AdmissionType::DISCHARGE);
        }

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.shorthand as typeShorthand,
                        f.address as address,
                        f.license as license,
                        f.phone as typePhone,
                        f.fax as typeFax,
                        fr.number as roomNumber,
                        fr.private as private,
                        fr.floor as floor,
                        fb.id as bedId,
                        fb.number as bedNumber,
                        ra.dnr as dnr,
                        ra.polst as polst,
                        ra.ambulatory as ambulatory,
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
                    ->orderBy('f.name')
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
                    ->orderBy('a.name')
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
                        'ra.address as address,
                        csz.city as city,
                        csz.stateAbbr as state,
                        csz.zipMain as zip,
                        reg.id as typeId,
                        reg.name as typeName,
                        reg.phone as typePhone,
                        reg.fax as typeFax,
                        ra.dnr as dnr,
                        ra.polst as polst,
                        ra.ambulatory as ambulatory,
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
                        CityStateZip::class,
                        'csz',
                        Join::WITH,
                        'ra.csz = csz'
                    )
                    ->innerJoin(
                        CareLevel::class,
                        'cl',
                        Join::WITH,
                        'ra.careLevel = cl'
                    );

                $qb
                    ->orderBy('reg.name');

                if ($typeId) {
                    $qb
                        ->andWhere('reg.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
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
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($notGrantResidentIds !== null) {
            $qb
                ->andWhere('r.id NOT IN (:notGrantResidentIds)')
                ->setParameter('notGrantResidentIds', $notGrantResidentIds);
        }

        if ($residentId) {
            $qb
                ->andWhere('r.id = :id')
                ->setParameter('id', $residentId);
        }

        return $qb
            ->groupBy('r.id')
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
            ->createQueryBuilder('r')
            ->select("CONCAT(r.firstName, ' ', r.lastName) as fullName");

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('r.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('r.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('r.id IN (:array)')
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
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
