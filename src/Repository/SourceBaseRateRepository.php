<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\CareLevel;
use App\Entity\PaymentSource;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class SourceBaseRateRepository
 * @package App\Repository
 */
class SourceBaseRateRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $paymentSource
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, $paymentSource)
    {
        $qb = $this
            ->createQueryBuilder('sbr')
            ->innerJoin(
                PaymentSource::class,
                'ps',
                Join::WITH,
                'ps = sbr.paymentSource'
            )
            ->where('ps = :paymentSource')
            ->setParameter('paymentSource', $paymentSource);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    CareLevel::class,
                    'cl',
                    Join::WITH,
                    'cl = sbr.careLevel'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = cl.space'
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
                    CareLevel::class,
                    'cl',
                    Join::WITH,
                    'cl = sbr.careLevel'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = cl.space'
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
                CareLevel::class,
                'cl',
                Join::WITH,
                'cl = sbr.careLevel'
            )
            ->select('sbr.amount as amount');

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
                    's = cl.space'
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