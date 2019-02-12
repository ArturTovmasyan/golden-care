<?php

namespace App\Repository;

use App\Entity\Medication;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class MedicationRepository
 * @package App\Repository
 */
class MedicationRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Medication::class, 'm')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = m.space'
            );

        if ($space !== null) {
            $queryBuilder
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        $queryBuilder
            ->groupBy('m.id');
    }

    /**
     * @param Space|null $space
     * @return mixed
     */
    public function list(Space $space = null)
    {
        $qb = $this
            ->createQueryBuilder('m')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = m.space'
            );

        if ($space !== null) {
            $qb
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
            ->createQueryBuilder('m')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = m.space'
            )
            ->where('m.id = :id')
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
        $qb = $this->createQueryBuilder('m');

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = m.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb->where($qb->expr()->in('m.id', $ids))
            ->groupBy('m.id')
            ->getQuery()
            ->getResult();
    }
}
