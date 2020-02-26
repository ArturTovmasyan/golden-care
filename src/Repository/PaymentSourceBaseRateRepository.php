<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\PaymentSource;
use App\Entity\PaymentSourceBaseRate;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PaymentSourceBaseRateRepository
 * @package App\Repository
 */
class PaymentSourceBaseRateRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     * @param null $paymentSourceId
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder, $paymentSourceId = null): void
    {
        $queryBuilder
            ->from(PaymentSourceBaseRate::class, 'sbr')
            ->addSelect('JSON_ARRAYAGG(CASE WHEN cl.title IS NOT NULL THEN JSON_OBJECT(cl.title, brl.amount) ELSE \'\' END) AS levels')
            ->innerJoin(
                PaymentSource::class,
                'ps',
                Join::WITH,
                'ps = sbr.paymentSource'
            )
            ->leftJoin('sbr.levels', 'brl')
            ->leftJoin('brl.careLevel', 'cl');

        if ($paymentSourceId !== null) {
            $queryBuilder
                ->andWhere('ps.id = :paymentSourceId')
                ->setParameter('paymentSourceId', $paymentSourceId);
        }

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ps.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('sbr.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->orderBy('sbr.date', 'DESC')
            ->groupBy('sbr.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param null $id
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, $id = null)
    {
        $qb = $this
            ->createQueryBuilder('sbr')
            ->innerJoin(
                PaymentSource::class,
                'ps',
                Join::WITH,
                'ps = sbr.paymentSource'
            );

        if ($id !== null) {
            $qb
                ->where('ps.id = :id')
                ->setParameter('id', $id);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ps.space'
                )
                ->andWhere('s = :space')
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('sbr.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('sbr.date', 'DESC')
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
            ->createQueryBuilder('sbr')
            ->innerJoin(
                PaymentSource::class,
                'ps',
                Join::WITH,
                'ps = sbr.paymentSource'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ps.space'
            )
            ->where('sbr.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('sbr.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $paymentSourceId
     * @param $date
     * @return mixed
     */
    public function getByDate(Space $space = null, array $entityGrants = null, $paymentSourceId, $date)
    {
        $qb = $this
            ->createQueryBuilder('sbr')
            ->innerJoin(
                PaymentSource::class,
                'ps',
                Join::WITH,
                'ps = sbr.paymentSource'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ps.space'
            )
            ->where('ps.id = :paymentSourceId')
            ->andWhere('sbr.date = :date')
            ->setParameter('paymentSourceId', $paymentSourceId)
            ->setParameter('date', $date);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('sbr.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
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
            ->createQueryBuilder('sbr')
            ->where('sbr.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    PaymentSource::class,
                    'ps',
                    Join::WITH,
                    'ps = sbr.paymentSource'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ps.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('sbr.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('sbr.id')
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
            ->createQueryBuilder('sbr')
            ->innerJoin(
                PaymentSource::class,
                'ps',
                Join::WITH,
                'ps = sbr.paymentSource'
            )
            ->select('sbr.date as date');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('sbr.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('sbr.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('sbr.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ps.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('sbr.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}