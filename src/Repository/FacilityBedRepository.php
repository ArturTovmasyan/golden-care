<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Space;
use App\Model\AdmissionType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityBedRepository
 * @package App\Repository
 */
class FacilityBedRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @param QueryBuilder $queryBuilder
     * @param null $facilityId
     */
    public function search(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null, QueryBuilder $queryBuilder, $facilityId = null): void
    {
        $queryBuilder
            ->from(FacilityBed::class, 'fb')
            ->addSelect('(SELECT DISTINCT CONCAT(COALESCE(r.firstName, \'\'), \' \', COALESCE(r.lastName, \'\')) FROM \App\Entity\ResidentAdmission ra JOIN ra.facilityBed afb JOIN ra.resident r WHERE afb.id=fb.id AND ra.admissionType < :admissionType AND ra.end IS NULL GROUP BY afb.id) AS resident')
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
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

        if ($facilityId !== null) {
            $queryBuilder
                ->where('f.id = :facilityId')
                ->setParameter('facilityId', $facilityId);
        }

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
                ->andWhere('fb.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $queryBuilder
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        $queryBuilder
            ->orderBy('f.shorthand', 'ASC')
            ->addOrderBy('fr.number', 'ASC')
            ->addOrderBy('fb.number', 'ASC')
            ->groupBy('fb.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @param null $facilityId
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null, $facilityId = null)
    {
        $qb = $this->createQueryBuilder('fb')
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
            );

        if ($facilityId !== null) {
            $qb
                ->where('f.id = :facilityId')
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
                ->andWhere('fb.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        return $qb
            ->orderBy('f.shorthand', 'ASC')
            ->addOrderBy('fr.number', 'ASC')
            ->addOrderBy('fb.number', 'ASC')
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
            ->createQueryBuilder('fb')
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
            ->where('fb.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fb.id IN (:grantIds)')
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
            ->createQueryBuilder('fb')
            ->where('fb.id IN (:ids)')
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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fb.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('fb.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $ids
     * @return mixed
     */
    public function getBedIdsByRooms(Space $space = null, array $entityGrants = null, $ids)
    {
        $qb = $this
            ->createQueryBuilder('fb')
            ->select(
                'fb.id AS id'
            )
            ->join('fb.room', 'r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Facility::class,
                    'f',
                    Join::WITH,
                    'f = r.facility'
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
                ->andWhere('fb.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $facilityId
     * @return mixed
     */
    public function getBedCount($facilityId)
    {
        $qb = $this
            ->createQueryBuilder('fb')
            ->select('COUNT(fb.id)')
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
            ->where('f.id = :facilityId AND fb.enabled=1')
            ->setParameter('facilityId', $facilityId);

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $ids
     * @param null $date
     * @return mixed
     */
    public function getBedIdAndTypeIdByRooms(Space $space = null, array $entityGrants = null, $ids, $date = null)
    {
        $qb = $this->createQueryBuilder('fb');

        $qb
            ->select(
                'fb.id AS id,
                type.id AS typeId,
                type.name AS typeName,
                r.number AS roomNumber,
                frt.private AS private,
                r.floor AS floor,
                r.notes AS notes,
                fb.number AS bedNumber
            ')
            ->join('fb.room', 'r')
            ->join('r.facility', 'type')
            ->join('r.type', 'frt')
            ->where('r.id IN (:ids)')
            ->andWhere('fb.enabled=1')
            ->setParameter('ids', $ids);

        if ($date !== null) {
            $qb
                ->andWhere('fb.billThroughDate IS NULL OR (fb.billThroughDate IS NOT NULL AND fb.billThroughDate < :date)')
                ->setParameter('date', $date);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = type.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fb.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('type.name')
            ->addOrderBy('r.number')
            ->addOrderBy('fb.number')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param null $typeId
     * @return mixed
     */
    public function getEnabledBeds(Space $space = null, array $entityGrants = null, $typeId = null)
    {
        $qb = $this->createQueryBuilder('fb');

        $qb
            ->select(
                'fb.id AS id,
                type.id AS typeId,
                type.name AS typeName,
                r.number AS roomNumber,
                frt.private AS private,
                fb.number AS bedNumber,
                fb.billThroughDate AS billThroughDate
            ')
            ->join('fb.room', 'r')
            ->join('r.facility', 'type')
            ->join('r.type', 'frt')
            ->andWhere('fb.enabled=1');

        if ($typeId !== null) {
            $qb
             ->andWhere('type.id = :typeId')
             ->setParameter('typeId', $typeId);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = type.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fb.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('type.name')
            ->addOrderBy('r.number')
            ->addOrderBy('fb.number')
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
            ->createQueryBuilder('fb')
            ->select('fb.number');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('fb.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('fb.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('fb.id IN (:array)')
                ->setParameter('array', []);
        }

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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fb.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}