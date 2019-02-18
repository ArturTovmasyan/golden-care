<?php

namespace App\Repository\Assessment;

use App\Entity\Assessment\CareLevel;
use App\Entity\Assessment\CareLevelGroup;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CareLevelRepository
 * @package App\Repository\Assessment
 */
class CareLevelRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(CareLevel::class, 'acl')
            ->innerJoin(
                CareLevelGroup::class,
                'aclg',
                Join::WITH,
                'aclg = acl.careLevelGroup'
            );

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = aclg.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        $queryBuilder
            ->groupBy('acl.id');
    }

    /**
     * @param Space|null $space
     * @return mixed
     */
    public function list(Space $space = null)
    {
        $qb = $this->createQueryBuilder('acl');

        if ($space !== null) {
            $qb
                ->innerJoin(
                    CareLevelGroup::class,
                    'aclg',
                    Join::WITH,
                    'aclg = acl.careLevelGroup'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = aclg.space'
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
            ->createQueryBuilder('acl')
            ->innerJoin(
                CareLevelGroup::class,
                'aclg',
                Join::WITH,
                'aclg = acl.careLevelGroup'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = aclg.space'
            )
            ->where('acl.id = :id')
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
        $qb = $this->createQueryBuilder('acl');

        $qb->where($qb->expr()->in('acl.id', $ids));

        if ($space !== null) {
            $qb
                ->innerJoin(
                    CareLevelGroup::class,
                    'aclg',
                    Join::WITH,
                    'aclg = acl.careLevelGroup'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = aclg.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb->groupBy('acl.id')
            ->getQuery()
            ->getResult();
    }
}