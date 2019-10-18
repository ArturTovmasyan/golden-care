<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\CityStateZip;
use App\Entity\Facility;
use App\Entity\Lead\CareType;
use App\Entity\Lead\Lead;
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
     * @param array|null $facilityEntityGrants
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder, $all, $userId = null, array $facilityEntityGrants = null) : void
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

        if ($userId !== null || $facilityEntityGrants !== null) {
            $queryBuilder
                ->andWhere('o.id = :userId')
                ->setParameter('userId', $userId);

            if ($facilityEntityGrants !== null) {
                $queryBuilder
                    ->leftJoin('l.facilities', 'sf')
                    ->orWhere('f.id IN (:facilityGrantIds) OR sf.id IN (:facilityGrantIds)')
                    ->setParameter('facilityGrantIds', $facilityEntityGrants);
            }
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
     * @param null $contactId
     * @param array|null $facilityEntityGrants
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null, $all, $free, $userId = null, array $facilityEntityGrants = null, $contactId = null)
    {
        $qb = $this
            ->createQueryBuilder('l')
            ->innerJoin(
                User::class,
                'o',
                Join::WITH,
                'o = l.owner'
            )
            ->leftJoin('l.referral', 'r');

        if (!$all) {
            $qb
                ->where('l.state = :state')
                ->setParameter('state', State::TYPE_OPEN);
        }

        if ($free) {
            $qb
                ->andWhere('r.id IS NULL');
        }

        if ($userId !== null || $facilityEntityGrants !== null) {
            $qb
                ->andWhere('o.id = :userId')
                ->setParameter('userId', $userId);

            if ($facilityEntityGrants !== null) {
                $qb
                    ->leftJoin(
                        Facility::class,
                        'f',
                        Join::WITH,
                        'f = l.primaryFacility'
                    )
                    ->leftJoin('l.facilities', 'sf')
                    ->orWhere('f.id IN (:facilityGrantIds) OR sf.id IN (:facilityGrantIds)')
                    ->setParameter('facilityGrantIds', $facilityEntityGrants);
            }
        }

        if ($contactId !== null) {
            $qb
                ->leftJoin('r.contact', 'c')
                ->andWhere('c.id = :contactId')
                ->setParameter('contactId', $contactId);
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
                'l', 'fas',
                'csz.city as rpCity',
                'csz.stateAbbr as rpStateAbbr',
                'csz.zipMain as rpZipMain',
                'ct.title as careType',
                'pt.title as paymentType',
                '(SELECT DISTINCT fs.title FROM App:Lead\LeadFunnelStage lfs JOIN lfs.stage fs JOIN lfs.lead fsl
                WHERE fsl.id=l.id
                AND lfs.date = (SELECT MAX(lfsMax.date) FROM App:Lead\LeadFunnelStage lfsMax JOIN lfsMax.lead fslMax WHERE fslMax.id=l.id)
                GROUP BY fsl.id
                ) as funnelStage',
                '(SELECT DISTINCT lf.date FROM App:Lead\LeadFunnelStage lf JOIN lf.lead lfl
                WHERE lfl.id=l.id
                AND lf.date = (SELECT MAX(lfMax.date) FROM App:Lead\LeadFunnelStage lfMax JOIN lfMax.lead lflMax WHERE lflMax.id=l.id)
                GROUP BY lfl.id
                ) as funnelDate',
                '(SELECT DISTINCT t.title FROM App:Lead\LeadTemperature lt JOIN lt.temperature t JOIN lt.lead ltl
                WHERE ltl.id=l.id
                AND lt.date = (SELECT MAX(ltMax.date) FROM App:Lead\LeadTemperature ltMax JOIN ltMax.lead ltlMax WHERE ltlMax.id=l.id)
                GROUP BY ltl.id
                ) as temperature',
                "CONCAT(o.firstName, ' ', o.lastName) as ownerFullName",
                "(CASE
                    WHEN r.id IS NOT NULL AND rc.id IS NOT NULL THEN CONCAT(rc.firstName, ' ', rc.lastName)
                    WHEN r.id IS NOT NULL AND rc.id IS NULL THEN ro.name
                    ELSE 'N/A' END) as referralFullName",
                'f.name as primaryFacility
                '
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
            ->leftJoin('l.facilities', 'fas')
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
