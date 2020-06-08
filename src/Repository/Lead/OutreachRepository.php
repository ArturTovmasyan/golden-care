<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Lead\Organization;
use App\Entity\Lead\OutreachType;
use App\Entity\Lead\Outreach;
use App\Entity\Space;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class OutreachRepository
 * @package App\Repository\Lead
 */
class OutreachRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->from(Outreach::class, 'ou')
            ->addSelect("GROUP_CONCAT(DISTINCT CONCAT(c.firstName, ' ', c.lastName) SEPARATOR ', ') AS contacts")
            ->addSelect("GROUP_CONCAT(DISTINCT CONCAT(p.firstName, ' ', p.lastName) SEPARATOR ', ') AS participants")
            ->innerJoin(
                OutreachType::class,
                'ot',
                Join::WITH,
                'ot = ou.type'
            )
            ->innerJoin('ou.contacts', 'c')
            ->innerJoin('ou.participants', 'p')
            ->leftJoin(
                Organization::class,
                'o',
                Join::WITH,
                'o = ou.organization'
            );

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ot.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('ou.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->addOrderBy('ou.createdAt', 'DESC')
            ->groupBy('ou.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null)
    {
        $qb = $this
            ->createQueryBuilder('ou')
            ->innerJoin(
                OutreachType::class,
                'ot',
                Join::WITH,
                'ot = ou.type'
            );

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ot.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ou.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $qb
            ->addOrderBy('ou.createdAt', 'DESC');

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
            ->createQueryBuilder('ou')
            ->innerJoin(
                OutreachType::class,
                'ot',
                Join::WITH,
                'ot = ou.type'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ot.space'
            )
            ->where('ou.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ou.id IN (:grantIds)')
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
            ->createQueryBuilder('ou')
            ->where('ou.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    OutreachType::class,
                    'ot',
                    Join::WITH,
                    'ot = ou.type'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ot.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ou.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('ou.id')
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
            ->createQueryBuilder('ou')
            ->innerJoin(
                OutreachType::class,
                'ot',
                Join::WITH,
                'ot = ou.type'
            )
            ->select('ot.title as title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('ou.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('ou.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('ou.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ot.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ou.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getOutreachList(Space $space = null, array $entityGrants = null, $startDate, $endDate)
    {
        $qb = $this
            ->createQueryBuilder('ou')
            ->select(
                'ou', 'c', 'p',
                'ot.title as typeTitle',
                'o.name as organizationName'
            )
            ->innerJoin(
                OutreachType::class,
                'ot',
                Join::WITH,
                'ot = ou.type'
            )
            ->innerJoin('ou.contacts', 'c')
            ->innerJoin('ou.participants', 'p')
            ->leftJoin(
                Organization::class,
                'o',
                Join::WITH,
                'o = ou.organization'
            )
            ->where('ou.createdAt >= :startDate')->setParameter('startDate', $startDate)
            ->andWhere('ou.createdAt < :endDate')->setParameter('endDate', $endDate);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ot.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ou.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    ///////////// For Facility Dashboard ///////////////////////////////////////////////////////////////////////////////

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getOutreachesForFacilityDashboard(Space $space = null, array $entityGrants = null, $startDate, $endDate)
    {
        $qb = $this
            ->createQueryBuilder('ou')
            ->select(
                'ou.id as id'
            )
            ->addSelect("GROUP_CONCAT(DISTINCT p.id SEPARATOR ',') AS participants")
            ->innerJoin('ou.participants', 'p')
            ->where('ou.createdAt >= :startDate AND ou.createdAt <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    OutreachType::class,
                    'ot',
                    Join::WITH,
                    'ot = ou.type'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ot.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ou.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('ou.id')
            ->getQuery()
            ->getResult();
    }
    ///////////////// End For Facility Dashboard ///////////////////////////////////////////////////////////////////////
}
