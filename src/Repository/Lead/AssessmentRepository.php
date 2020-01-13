<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Lead\Assessment;
use App\Entity\Assessment\Form;
use App\Entity\Lead\Lead;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AssessmentRepository
 * @package App\Repository\Lead
 */
class AssessmentRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->from(Assessment::class, 'a')
            ->innerJoin(
                Form::class,
                'f',
                Join::WITH,
                'f = a.form'
            )
            ->innerJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = a.lead'
            );

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = f.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('a.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->innerJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = a.lead'
            )
            ->where('l.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Form::class,
                    'f',
                    Join::WITH,
                    'f = a.form'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = f.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
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
            ->createQueryBuilder('a')
            ->innerJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = a.lead'
            )
            ->innerJoin(
                Form::class,
                'f',
                Join::WITH,
                'f = a.form'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = f.space'
            )
            ->where('a.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
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
            ->createQueryBuilder('a')
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Form::class,
                    'f',
                    Join::WITH,
                    'f = a.form'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = f.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('a.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param null $mappedBy
     * @param null $id
     * @param array|null $ids
     * @return mixed
     */
    public function getRelatedData(Space $space = null, array $entityGrants = null, $mappedBy = null, $id = null, array $ids = null)
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->select('a.score');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('a.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('a.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('a.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Form::class,
                    'f',
                    Join::WITH,
                    'f = a.form'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = f.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}