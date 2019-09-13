<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Lead\Contact;
use App\Entity\Lead\Lead;
use App\Entity\Lead\Organization;
use App\Entity\Lead\ReferrerType;
use App\Entity\Lead\Referral;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ReferralRepository
 * @package App\Repository\Lead
 */
class ReferralRepository extends EntityRepository  implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     * @param null $organizationId
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder, $organizationId = null) : void
    {
        $queryBuilder
            ->from(Referral::class, 'r')
            ->innerJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = r.lead'
            )
            ->innerJoin(
                ReferrerType::class,
                'rt',
                Join::WITH,
                'rt = r.type'
            )
            ->leftJoin(
                Organization::class,
                'o',
                Join::WITH,
                'o = r.organization'
            )
            ->leftJoin(
                Contact::class,
                'c',
                Join::WITH,
                'c = r.contact'
            );

        if ($organizationId !== null) {
            $queryBuilder
                ->where('o.id = :organizationId')
                ->setParameter('organizationId', $organizationId);
        }

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rt.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('r.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->innerJoin(
                ReferrerType::class,
                'rt',
                Join::WITH,
                'rt = r.type'
            );

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rt.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $organizationId
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, $organizationId)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->innerJoin(
                ReferrerType::class,
                'rt',
                Join::WITH,
                'rt = r.type'
            )
            ->leftJoin(
                Organization::class,
                'o',
                Join::WITH,
                'o = r.organization'
            )
            ->where('o.id = :organizationId')
            ->setParameter('organizationId', $organizationId);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rt.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
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
            ->createQueryBuilder('r')
            ->innerJoin(
                ReferrerType::class,
                'rt',
                Join::WITH,
                'rt = r.type'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = rt.space'
            )
            ->where('r.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
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
            ->createQueryBuilder('r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ReferrerType::class,
                    'rt',
                    Join::WITH,
                    'rt = r.type'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rt.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $leadId
     * @param null $id
     * @return mixed
     */
    public function getByLeadWithoutCurrent(Space $space = null, array $entityGrants = null, $leadId, $id = null)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->innerJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = r.lead'
            )
            ->where('l.id = :leadId')
            ->setParameter('leadId', $leadId);

        if ($id !== null) {
            $qb
                ->andWhere('r.id != :id')
                ->setParameter('id', $id);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ReferrerType::class,
                    'rt',
                    Join::WITH,
                    'rt = r.type'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rt.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('r.id')
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
            ->createQueryBuilder('r')
            ->leftJoin(
                Organization::class,
                'o',
                Join::WITH,
                'o = r.organization'
            )
            ->leftJoin(
                Contact::class,
                'c',
                Join::WITH,
                'c = r.contact'
            )
            ->select("CASE WHEN c.id IS NOT NULL THEN CONCAT(c.firstName, ' ', c.lastName) ELSE o.name END as name");

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('r.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('r.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('r.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ReferrerType::class,
                    'rt',
                    Join::WITH,
                    'rt = r.type'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rt.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
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
    public function getReferralList(Space $space = null, array $entityGrants = null, $startDate, $endDate)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->select(
                'r.id as id',
                'c.id as cId',
                'c.firstName as firstName',
                'c.lastName as lastName',
                'rt.title as typeTitle',
                'o.name as orgTitle',
                "CONCAT(l.firstName, ' ', l.lastName) AS leadName",
                'c.emails as emails',
                'r.notes as notes'
            )
            ->innerJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = r.lead'
            )
            ->innerJoin(
                ReferrerType::class,
                'rt',
                Join::WITH,
                'rt = r.type'
            )
            ->leftJoin(
                Organization::class,
                'o',
                Join::WITH,
                'o = r.organization'
            )
            ->leftJoin(
                Contact::class,
                'c',
                Join::WITH,
                'c = r.contact'
            )
            ->where('r.createdAt >= :startDate')->setParameter('startDate', $startDate)
            ->andWhere('r.createdAt < :endDate')->setParameter('endDate', $endDate);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rt.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('r.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
