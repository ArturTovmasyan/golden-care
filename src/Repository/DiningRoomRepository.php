<?php

namespace App\Repository;

use App\Entity\DiningRoom;
use App\Entity\Facility;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DiningRoomRepository
 * @package App\Repository
 */
class DiningRoomRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(DiningRoom::class, 'dr')
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = dr.facility'
            )
            ->groupBy('dr.id');
    }

    /**
     * @param Space|null $space
     * @param $id
     * @return mixed
     */
    public function getOne(Space $space = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('dr')
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = dr.facility'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = f.space'
            )
            ->where('dr.id = :id')
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
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('dr');

        return $qb->where($qb->expr()->in('dr.id', $ids))
            ->groupBy('dr.id')
            ->getQuery()
            ->getResult();
    }
}