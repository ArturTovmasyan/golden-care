<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\FacilityRoomType;
use App\Entity\Facility;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityRoomTypeRepository
 * @package App\Repository
 */
class FacilityRoomTypeRepository extends EntityRepository implements RelatedInfoInterface
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
            ->from(FacilityRoomType::class, 'frt')
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = frt.facility'
            );

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
                ->andWhere('frt.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $queryBuilder
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        $queryBuilder
            ->groupBy('frt.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null)
    {
        $qb = $this->createQueryBuilder('frt');

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
                ->andWhere('frt.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        $qb
            ->addOrderBy('frt.title', 'ASC');

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @param $id
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('frt')
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = frt.facility'
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
                ->andWhere('frt.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @param $id
     * @return mixed
     */
    public function getOne(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('frt')
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
            ->where('frt.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('frt.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @param $ids
     * @return mixed
     */
    public function findByIds(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null, $ids)
    {
        $qb = $this
            ->createQueryBuilder('frt')
            ->where('frt.id IN (:ids)')
            ->setParameter('ids', $ids);

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

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('frt.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('frt.id')
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
            ->createQueryBuilder('frt')
            ->select('frt.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('frt.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('frt.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('frt.id IN (:array)')
                ->setParameter('array', []);
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
                ->andWhere('frt.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}