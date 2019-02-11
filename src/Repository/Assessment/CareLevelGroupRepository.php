<?php

namespace App\Repository\Assessment;

use App\Entity\Assessment\CareLevelGroup;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CareLevelGroupRepository
 * @package App\Repository\Assessment
 */
class CareLevelGroupRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(CareLevelGroup::class, 'aclg')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = aclg.space'
            );

        if ($space !== null) {
            $queryBuilder
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        $queryBuilder
            ->groupBy('aclg.id');
    }

    /**
     * @param Space|null $space
     * @return mixed
     */
    public function list(Space $space = null)
    {
        $qb = $this
            ->createQueryBuilder('aclg')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = aclg.space'
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
            ->createQueryBuilder('aclg')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = aclg.space'
            )
            ->where('aclg.id = :id')
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
        $qb = $this->createQueryBuilder('aclg');

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = aclg.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb->where($qb->expr()->in('aclg.id', $ids))
            ->groupBy('aclg.id')
            ->getQuery()
            ->getResult();
    }
}