<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\ApartmentRoom;
use App\Entity\Apartment;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ApartmentRoomRepository
 * @package App\Repository
 */
class ApartmentRoomRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $apartmentEntityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, array $apartmentEntityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(ApartmentRoom::class, 'ar')
            ->innerJoin(
                Apartment::class,
                'a',
                Join::WITH,
                'a = ar.apartment'
            );

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
                ->andWhere('ar.id IN (:grantIds)')
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
            ->groupBy('ar.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $apartmentEntityGrants
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null, array $apartmentEntityGrants = null)
    {
        $qb = $this->createQueryBuilder('ar');

        if ($space !== null) {
            $qb
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
                ->andWhere('ar.id IN (:grantIds)')
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
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $apartmentEntityGrants
     * @param $id
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, array $apartmentEntityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('ar')
            ->innerJoin(
                Apartment::class,
                'a',
                Join::WITH,
                'a = ar.apartment'
            )
            ->where('a.id = :id')
            ->setParameter('id', $id);

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
                ->andWhere('ar.id IN (:grantIds)')
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
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $apartmentEntityGrants
     * @param $id
     * @return mixed
     */
    public function getOne(Space $space = null, array $entityGrants = null, array $apartmentEntityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('ar')
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
            ->where('ar.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ar.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($apartmentEntityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:apartmentGrantIds)')
                ->setParameter('apartmentGrantIds', $apartmentEntityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $apartmentEntityGrants
     * @param $ids
     * @return mixed
     */
    public function findByIds(Space $space = null, array $entityGrants = null, array $apartmentEntityGrants = null, $ids)
    {
        $qb = $this
            ->createQueryBuilder('ar')
            ->where('ar.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
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
                ->andWhere('ar.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($apartmentEntityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:apartmentGrantIds)')
                ->setParameter('apartmentGrantIds', $apartmentEntityGrants);
        }

        return $qb->groupBy('ar.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $apartmentId
     * @return mixed
     */
    public function getLastNumber(Space $space = null, array $entityGrants = null, $apartmentId) {
        $qb = $this
            ->createQueryBuilder('ar')
            ->select('MAX(ar.number) as max_room_number')
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
            ->where('a.id = :apartment_id')
            ->setParameter('apartment_id', $apartmentId);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ar.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
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
            ->createQueryBuilder('ar')
            ->select('ar.number');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('ar.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('ar.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('ar.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
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
                ->andWhere('ar.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}