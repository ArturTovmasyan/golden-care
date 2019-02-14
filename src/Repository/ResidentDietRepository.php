<?php

namespace App\Repository;

use App\Entity\Diet;
use App\Entity\ResidentDiet;
use App\Entity\Resident;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentDietRepository
 * @package App\Repository
 */
class ResidentDietRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentDiet::class, 'rd')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rd.resident'
            )
            ->innerJoin(
                Diet::class,
                'd',
                Join::WITH,
                'd = rd.diet'
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

        $queryBuilder
            ->groupBy('rd.id');
    }

    /**
     * @param Space|null $space
     * @param $id
     * @return mixed
     */
    public function getBy(Space $space = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('rd')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rd.resident'
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

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param $id
     * @return mixed
     */
    public function getOne(Space $space = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('rd')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rd.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('rd.id = :id')
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
        $qb = $this->createQueryBuilder('rd');

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rd.resident'
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

        return $qb->where($qb->expr()->in('rd.id', $ids))
            ->groupBy('rd.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(Space $space = null, array $residentIds)
    {
        $qb = $this->createQueryBuilder('rd');

        $qb
            ->select('
                    d.id as id,
                    d.title as title,
                    d.color as color,
                    rd.description as description,
                    r.id as residentId
            ')
            ->innerJoin(
                Diet::class,
                'd',
                Join::WITH,
                'rd.diet = d'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'rd.resident = r'
            );

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

        return $qb
            ->where($qb->expr()->in('r.id', $residentIds))
            ->orderBy('d.title')
            ->groupBy('rd.id')
            ->getQuery()
            ->getResult();
    }
}