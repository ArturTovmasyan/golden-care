<?php

namespace App\Repository;

use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityBedRepository
 * @package App\Repository
 */
class FacilityBedRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(Space $space = null, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(FacilityBed::class, 'fb')
            ->innerJoin(
                FacilityRoom::class,
                'fr',
                Join::WITH,
                'fr = fb.room'
            );

        if ($space !== null) {
            $queryBuilder
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

        $queryBuilder
            ->groupBy('fb.id');
    }

    /**
     * @param Space|null $space
     * @param $id
     * @return mixed
     */
    public function getOne(Space $space = null, $id)
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
        $qb = $this->createQueryBuilder('fb');

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

        return $qb->where($qb->expr()->in('fb.id', $ids))
            ->groupBy('fb.id')
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

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function getBedIdAndTypeIdByRooms($ids)
    {
        $qb = $this->createQueryBuilder('fb');

        return $qb
            ->select(
                'fb.id AS id,
                type.id AS typeId,
                type.name AS typeName,
                r.number AS roomNumber,
                r.notes AS notes,
                fb.number AS bedNumber
            ')
            ->join('fb.room', 'r')
            ->join('r.facility', 'type')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('type.name')
            ->addOrderBy('r.number')
            ->addOrderBy('fb.number')
            ->getQuery()
            ->getResult();
    }
}