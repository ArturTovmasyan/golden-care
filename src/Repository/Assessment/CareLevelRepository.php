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
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
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

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('acl.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('acl.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null)
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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('acl.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @return mixed
     */
    public function getOne(Space $space = null, array $entityGrants = null, $id)
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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('acl.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $ids
     * @return mixed
     */
    public function findByIds(Space $space = null, array $entityGrants = null, $ids)
    {
        $qb = $this
            ->createQueryBuilder('acl')
            ->where('acl.id IN (:ids)')
            ->setParameter('ids', $ids);

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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('acl.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('acl.id')
            ->getQuery()
            ->getResult();
    }
}