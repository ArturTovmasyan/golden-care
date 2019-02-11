<?php

namespace App\Repository\Assessment;

use App\Entity\Assessment\Form;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FormRepository
 * @package App\Repository\Assessment
 */
class FormRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Form::class, 'af')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = af.space'
            );

        if ($space !== null) {
            $queryBuilder
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        $queryBuilder
            ->groupBy('af.id');
    }

    /**
     * @param Space|null $space
     * @return mixed
     */
    public function list(Space $space = null)
    {
        $qb = $this
            ->createQueryBuilder('af')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = af.space'
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
            ->createQueryBuilder('af')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = af.space'
            )
            ->where('af.id = :id')
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
        $qb = $this->createQueryBuilder('af');

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = af.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb->where($qb->expr()->in('af.id', $ids))
            ->groupBy('af.id')
            ->getQuery()
            ->getResult();
    }
}