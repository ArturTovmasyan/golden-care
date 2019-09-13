<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\CityStateZip;
use App\Entity\Facility;
use App\Entity\Lead\CareType;
use App\Entity\Lead\Lead;
use App\Entity\Lead\Referral;
use App\Entity\Lead\StateChangeReason;
use App\Entity\PaymentSource;
use App\Entity\Space;
use App\Entity\User;
use App\Model\Lead\State;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class LeadRepository
 * @package App\Repository\Lead
 */
class LeadRepository extends EntityRepository  implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     * @param $all
     * @param null $userId
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder, $all, $userId = null) : void
    {
        $queryBuilder
            ->from(Lead::class, 'l')
            ->innerJoin(
                User::class,
                'o',
                Join::WITH,
                'o = l.owner'
            )
            ->leftJoin(
                CareType::class,
                'ct',
                Join::WITH,
                'ct = l.careType'
            )
            ->leftJoin(
                PaymentSource::class,
                'pt',
                Join::WITH,
                'pt = l.paymentType'
            )
            ->leftJoin(
                StateChangeReason::class,
                'scr',
                Join::WITH,
                'scr = l.stateChangeReason'
            )
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'csz = l.responsiblePersonCsz'
            )
            ->leftJoin('l.referral', 'r')
            ->leftJoin('r.organization', 'ro')
            ->leftJoin('r.contact', 'rc')
            ->leftJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = l.primaryFacility'
            );

        if (!$all) {
            $queryBuilder
                ->where('l.state = :state')
                ->setParameter('state', State::TYPE_OPEN);
        }

        if ($userId !== null) {
            $queryBuilder
                ->andWhere('o.id = :userId')
                ->setParameter('userId', $userId);
        }

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = o.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('l.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->addOrderBy('l.createdAt', 'DESC')
            ->groupBy('l.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $all
     * @param $free
     * @param null $userId
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null, $all, $free, $userId = null)
    {
        $qb = $this
            ->createQueryBuilder('l')
            ->innerJoin(
                User::class,
                'o',
                Join::WITH,
                'o = l.owner'
            );

        if (!$all) {
            $qb
                ->where('l.state = :state')
                ->setParameter('state', State::TYPE_OPEN);
        }

        if ($free) {
            $qb
                ->leftJoin('l.referral', 'r')
                ->andWhere('r.id IS NULL');
        }

        if ($userId !== null) {
            $qb
                ->andWhere('o.id = :userId')
                ->setParameter('userId', $userId);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = o.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('l.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $qb
            ->addOrderBy('l.createdAt', 'DESC');

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
            ->createQueryBuilder('l')
            ->innerJoin(
                User::class,
                'o',
                Join::WITH,
                'o = l.owner'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = o.space'
            )
            ->where('l.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('l.id IN (:grantIds)')
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
            ->createQueryBuilder('l')
            ->where('l.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    User::class,
                    'o',
                    Join::WITH,
                    'o = l.owner'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = o.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('l.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('l.id')
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
            ->createQueryBuilder('l')
            ->select("CONCAT(l.firstName, ' ', l.lastName) as fullName");

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('l.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('l.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('l.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    User::class,
                    'o',
                    Join::WITH,
                    'o = l.owner'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = o.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('l.id IN (:grantIds)')
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
    public function getLeadList(Space $space = null, array $entityGrants = null, $startDate, $endDate)
    {
        $qb = $this
            ->createQueryBuilder('l')
            ->select(
                'l', 'fs',
                'csz.city as rpCity',
                'csz.stateAbbr as rpStateAbbr',
                'csz.zipMain as rpZipMain',
                'ct.title as careType',
                'pt.title as paymentType',
                'scr.title as stateChangeReason',
                "CONCAT(o.firstName, ' ', o.lastName) as ownerFullName",
                "(CASE
                    WHEN r.id IS NOT NULL AND rc.id IS NOT NULL THEN CONCAT(rc.firstName, ' ', rc.lastName)
                    WHEN r.id IS NOT NULL AND rc.id IS NULL THEN ro.name
                    ELSE 'N/A' END) as referralFullName",
                'f.name as primaryFacility'
            )
            ->innerJoin(
                User::class,
                'o',
                Join::WITH,
                'o = l.owner'
            )
            ->leftJoin(
                CareType::class,
                'ct',
                Join::WITH,
                'ct = l.careType'
            )
            ->leftJoin(
                PaymentSource::class,
                'pt',
                Join::WITH,
                'pt = l.paymentType'
            )
            ->leftJoin(
                StateChangeReason::class,
                'scr',
                Join::WITH,
                'scr = l.stateChangeReason'
            )
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'csz = l.responsiblePersonCsz'
            )
            ->leftJoin('l.referral', 'r')
            ->leftJoin('r.organization', 'ro')
            ->leftJoin('r.contact', 'rc')
            ->leftJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = l.primaryFacility'
            )
            ->leftJoin('l.facilities', 'fs')
            ->where('l.createdAt >= :startDate')->setParameter('startDate', $startDate)
            ->andWhere('l.createdAt < :endDate')->setParameter('endDate', $endDate);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = o.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('l.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }
}
