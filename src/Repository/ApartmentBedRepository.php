<?php

namespace App\Repository;

use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ApartmentBedRepository
 * @package App\Repository
 */
class ApartmentBedRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(Space $space = null, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ApartmentBed::class, 'ab')
            ->innerJoin(
                ApartmentRoom::class,
                'ar',
                Join::WITH,
                'ar = ab.room'
            );

            if ($space !== null) {
                $queryBuilder
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

        $queryBuilder
            ->groupBy('ab.id');
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
     * @param $ids
     * @return mixed
     */
    public function findByIds(Space $space = null, $ids)
    {
        $qb = $this->createQueryBuilder('ab');

        $qb->where($qb->expr()->in('ab.id', $ids));

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

        return $qb->groupBy('ab.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param $ids
     * @return mixed
     */
    public function getBedIdsByRooms(Space $space = null, $ids)
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

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param $ids
     * @return mixed
     */
    public function getBedIdAndTypeIdByRooms(Space $space = null, $ids)
    {
        $qb = $this->createQueryBuilder('ab');

        $qb
            ->select(
                'ab.id AS id,
                type.id AS typeId,
                type.name AS typeName,
                r.number AS roomNumber,
                r.notes AS notes,
                ab.number AS bedNumber
            ')
            ->join('ab.room', 'r')
            ->join('r.apartment', 'type')
            ->where('r.id IN (:ids)')
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

        return $qb
            ->orderBy('type.name')
            ->addOrderBy('r.number')
            ->addOrderBy('ab.number')
            ->getQuery()
            ->getResult();
    }
}