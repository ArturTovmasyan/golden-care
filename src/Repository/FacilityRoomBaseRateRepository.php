<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Facility;
use App\Entity\FacilityRoomBaseRate;
use App\Entity\FacilityRoomType;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityRoomBaseRateRepository
 * @package App\Repository
 */
class FacilityRoomBaseRateRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     * @param null $roomTypeId
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder, $roomTypeId = null): void
    {
        $queryBuilder
            ->from(FacilityRoomBaseRate::class, 'br')
            ->addSelect('JSON_ARRAYAGG(JSON_OBJECT(cl.title, brl.amount)) AS base_rates')
            ->innerJoin(
                FacilityRoomType::class,
                'frt',
                Join::WITH,
                'frt = br.roomType'
            )
            ->join('br.levels', 'brl')
            ->join('brl.careLevel', 'cl');

        if ($roomTypeId !== null) {
            $queryBuilder
                ->andWhere('frt.id = :roomTypeId')
                ->setParameter('roomTypeId', $roomTypeId);
        }

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Facility::class,
                    'f',
                    Join::WITH,
                    'f = frt.facility'
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
            $queryBuilder
                ->andWhere('br.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->orderBy('br.date', 'DESC')
            ->groupBy('br.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param null $id
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, $id = null)
    {
        $qb = $this
            ->createQueryBuilder('br')
            ->innerJoin(
                FacilityRoomType::class,
                'frt',
                Join::WITH,
                'frt = br.roomType'
            );

        if ($id !== null) {
            $qb
                ->where('frt.id = :id')
                ->setParameter('id', $id);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Facility::class,
                    'f',
                    Join::WITH,
                    'f = frt.facility'
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
                ->andWhere('br.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('br.date', 'DESC')
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
            ->createQueryBuilder('br')
            ->innerJoin(
                FacilityRoomType::class,
                'frt',
                Join::WITH,
                'frt = br.roomType'
            )
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = frt.facility'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = f.space'
            )
            ->where('br.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('br.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $roomTypeId
     * @param $date
     * @return mixed
     */
    public function getByDate(Space $space = null, array $entityGrants = null, $roomTypeId, $date)
    {
        $qb = $this
            ->createQueryBuilder('br')
            ->innerJoin(
                FacilityRoomType::class,
                'frt',
                Join::WITH,
                'frt = br.roomType'
            )
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = frt.facility'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = f.space'
            )
            ->where('frt.id = :roomTypeId')
            ->andWhere('br.date = :date')
            ->setParameter('roomTypeId', $roomTypeId)
            ->setParameter('date', $date);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('br.id IN (:grantIds)')
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
    public function findByIds(Space $space = null, array $entityGrants = null, $ids)
    {
        $qb = $this
            ->createQueryBuilder('br')
            ->where('br.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    FacilityRoomType::class,
                    'frt',
                    Join::WITH,
                    'frt = br.roomType'
                )
                ->innerJoin(
                    Facility::class,
                    'f',
                    Join::WITH,
                    'f = frt.facility'
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
                ->andWhere('br.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('br.id')
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
            ->createQueryBuilder('br')
            ->innerJoin(
                FacilityRoomType::class,
                'frt',
                Join::WITH,
                'frt = br.roomType'
            )
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = frt.facility'
            )
            ->select('br.date as date');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('br.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('br.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('br.id IN (:array)')
                ->setParameter('array', []);
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
                ->andWhere('br.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}