<?php

namespace App\Repository\Assessment;

use App\Entity\Assessment\Category;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CategoryRepository
 * @package App\Repository
 */
class CategoryRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Category::class, 'ac')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ac.space'
            );

        if ($space !== null) {
            $queryBuilder
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        $queryBuilder
            ->groupBy('ac.id');
    }

    /**
     * @param Space|null $space
     * @return mixed
     */
    public function list(Space $space = null)
    {
        $qb = $this
            ->createQueryBuilder('ac')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ac.space'
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
            ->createQueryBuilder('ac')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ac.space'
            )
            ->where('ac.id = :id')
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
        $qb = $this->createQueryBuilder('ac');

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ac.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb->where($qb->expr()->in('ac.id', $ids))
            ->groupBy('ac.id')
            ->getQuery()
            ->getResult();
    }
}
