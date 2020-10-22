<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\DiscountItem;
use App\Entity\Resident;
use App\Entity\ResidentDiscountItem;
use App\Entity\ResidentLedger;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentDiscountItemRepository
 * @package App\Repository
 */
class ResidentDiscountItemRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->from(ResidentDiscountItem::class, 'rdi')
            ->innerJoin(
                ResidentLedger::class,
                'rl',
                Join::WITH,
                'rl = rdi.ledger'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rl.resident'
            )
            ->innerJoin(
                DiscountItem::class,
                'di',
                Join::WITH,
                'di = rdi.discountItem'
            );

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('rdi.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->orderBy('rdi.date', 'DESC')
            ->groupBy('rdi.id');
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
            ->createQueryBuilder('rdi')
            ->innerJoin(
                ResidentLedger::class,
                'rl',
                Join::WITH,
                'rl = rdi.ledger'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rl.resident'
            )
            ->where('rl.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rdi.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('rdi.date', 'DESC')
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
            ->createQueryBuilder('rdi')
            ->innerJoin(
                ResidentLedger::class,
                'rl',
                Join::WITH,
                'rl = rdi.ledger'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rl.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('rdi.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rdi.id IN (:grantIds)')
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
            ->createQueryBuilder('rdi')
            ->where('rdi.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ResidentLedger::class,
                    'rl',
                    Join::WITH,
                    'rl = rdi.ledger'
                )
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rl.resident'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rdi.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('rdi.id')
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
            ->createQueryBuilder('rdi')
            ->select('rdi.amount');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('rdi.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('rdi.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('rdi.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ResidentLedger::class,
                    'rl',
                    Join::WITH,
                    'rl = rdi.ledger'
                )
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rl.resident'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rdi.id IN (:grantIds)')
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
     * @param $startDate
     * @param $endDate
     * @return int|mixed|string
     */
    public function getByInterval(Space $space = null, array $entityGrants = null, $id, $startDate, $endDate)
    {
        $qb = $this
            ->createQueryBuilder('rdi')
            ->select('rdi.amount')
            ->innerJoin(
                ResidentLedger::class,
                'rl',
                Join::WITH,
                'rl = rdi.ledger'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rl.resident'
            )
            ->where('rl.id = :id')
            ->andWhere('rdi.date >= :startDate AND rdi.date <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rdi.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('rdi.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}