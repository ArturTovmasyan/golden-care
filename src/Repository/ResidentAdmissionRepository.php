<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Entity\ApartmentBed;
use App\Entity\CareLevel;
use App\Entity\CityStateZip;
use App\Entity\DiningRoom;
use App\Entity\FacilityBed;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentAdmission;
use App\Entity\Space;
use App\Model\AdmissionType;
use App\Model\GroupType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentAdmissionRepository
 * @package App\Repository
 */
class ResidentAdmissionRepository extends EntityRepository
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
                ApartmentBed::class,
                'ab',
                Join::WITH,
                'ab = ra.apartmentBed'
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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
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
//            ->andWhere('ra.id IN (SELECT MAX(mra.id)
//                        FROM App:ResidentAdmission mra
//                        JOIN mra.resident res
//                        WHERE mra.admissionType < :admissionType AND mra.end IS NULL
//                        GROUP BY res.id)'
//            )
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
                    ->setParameter('id', $id);
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
                    ->setParameter('id', $id);
                break;
            case GroupType::TYPE_REGION:
                $qb
                    ->join('ra.region', 'reg')
                    ->andWhere('reg.id=:id')
                    ->setParameter('id', $id);
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}