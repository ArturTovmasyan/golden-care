<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\Space;
use App\Model\AdmissionType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ApartmentBedRepository
 * @package App\Repository
 */
class ApartmentBedRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $apartmentEntityGrants
     * @param QueryBuilder $queryBuilder
     * @param null $apartmentId
     */
    public function search(Space $space = null, array $entityGrants = null, array $apartmentEntityGrants = null, QueryBuilder $queryBuilder, $apartmentId = null): void
    {
        $queryBuilder
            ->from(ApartmentBed::class, 'ab')
            ->addSelect('(SELECT DISTINCT CONCAT(COALESCE(r.firstName, \'\'), \' \', COALESCE(r.lastName, \'\')) FROM \App\Entity\ResidentAdmission ra JOIN ra.apartmentBed aab JOIN ra.resident r WHERE aab.id=ab.id AND ra.admissionType < :admissionType AND ra.end IS NULL) AS resident')
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
            ->setParameter('admissionType', AdmissionType::DISCHARGE);

        if ($apartmentId !== null) {
            $queryBuilder
                ->where('a.id = :apartmentId')
                ->setParameter('apartmentId', $apartmentId);
        }

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = a.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('ab.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($apartmentEntityGrants !== null) {
            $queryBuilder
                ->andWhere('a.id IN (:apartmentGrantIds)')
                ->setParameter('apartmentGrantIds', $apartmentEntityGrants);
        }

        $queryBuilder
            ->orderBy('a.shorthand', 'ASC')
            ->addOrderBy('ar.number', 'ASC')
            ->addOrderBy('ab.number', 'ASC')
            ->groupBy('ab.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $apartmentEntityGrants
     * @param null $apartmentId
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null, array $apartmentEntityGrants = null, $apartmentId = null)
    {
        $qb = $this->createQueryBuilder('ab')
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
            );

        if ($apartmentId !== null) {
            $qb
                ->where('a.id = :apartmentId')
                ->setParameter('apartmentId', $apartmentId);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = a.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ab.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($apartmentEntityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:apartmentGrantIds)')
                ->setParameter('apartmentGrantIds', $apartmentEntityGrants);
        }

        return $qb
            ->orderBy('a.shorthand', 'ASC')
            ->addOrderBy('ar.number', 'ASC')
            ->addOrderBy('ab.number', 'ASC')
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
            ->createQueryBuilder('ab')
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
            ->where('ab.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ab.id IN (:grantIds)')
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
            ->createQueryBuilder('ab')
            ->where('ab.id IN (:ids)')
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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ab.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('ab.id')
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
            ->createQueryBuilder('ab')
            ->select(
                'ab.id AS id'
            )
            ->join('ab.room', 'r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Apartment::class,
                    'a',
                    Join::WITH,
                    'a = r.apartment'
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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ab.id IN (:grantIds)')
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
     * @return mixed
     */
    public function getBedIdAndTypeIdByRooms(Space $space = null, array $entityGrants = null, $ids)
    {
        $qb = $this->createQueryBuilder('ab');

        $qb
            ->select(
                'ab.id AS id,
                type.id AS typeId,
                type.name AS typeName,
                r.number AS roomNumber,
                r.private AS private,
                r.floor AS floor,
                r.notes AS notes,
                ab.number AS bedNumber
            ')
            ->join('ab.room', 'r')
            ->join('r.apartment', 'type')
            ->where('r.id IN (:ids)')
            ->andWhere('ab.enabled=1')
            ->setParameter('ids', $ids);

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
                ->andWhere('ab.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('type.name')
            ->addOrderBy('r.number')
            ->addOrderBy('ab.number')
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
            ->createQueryBuilder('ab')
            ->select('ab.number');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('ab.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('ab.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('ab.id IN (:array)')
                ->setParameter('array', []);
        }

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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ab.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}