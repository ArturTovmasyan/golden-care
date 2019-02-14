<?php

namespace App\Repository;

use App\Entity\Resident;
use App\Entity\Contract;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ContractRepository
 * @package App\Repository
 */
class ContractRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Contract::class, 'c')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = c.resident'
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
            ->groupBy('c.id');
    }

    /**
     * @param Space|null $space
     * @param $id
     * @return mixed
     */
    public function getBy(Space $space = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = c.resident'
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
            ->createQueryBuilder('c')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = c.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('c.id = :id')
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
     * @param $id
     * @return mixed
     */
    public function getOneByEndDateNull(Space $space = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = c.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('r.id = :id AND c.end IS NULL')
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
        $qb = $this->createQueryBuilder('c');

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = c.resident'
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

        return $qb->where($qb->expr()->in('c.id', $ids))
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }
}