<?php

namespace App\Repository;

use App\Entity\EventDefinition;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class EventDefinitionRepository
 * @package App\Repository
 */
class EventDefinitionRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(EventDefinition::class, 'ed')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ed.space'
            );

        if ($space !== null) {
            $queryBuilder
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        $queryBuilder
            ->groupBy('ed.id');
    }

    /**
     * @param Space|null $space
     * @return mixed
     */
    public function list(Space $space = null)
    {
        $qb = $this
            ->createQueryBuilder('ed')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ed.space'
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
            ->createQueryBuilder('ed')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ed.space'
            )
            ->where('ed.id = :id')
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
        $qb = $this->createQueryBuilder('ed');

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ed.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb->where($qb->expr()->in('ed.id', $ids))
            ->groupBy('ed.id')
            ->getQuery()
            ->getResult();
    }
}