<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Lead\Referral;
use App\Entity\Lead\ReferrerType;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class ReferralPhoneRepository
 * @package App\Repository
 */
class ReferralPhoneRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param Referral $referral
     * @return mixed
     *
     */
    public function getBy(Space $space = null, array $entityGrants = null, Referral $referral)
    {
        $qb = $this
            ->createQueryBuilder('rp')
            ->innerJoin(
                Referral::class,
                'r',
                Join::WITH,
                'r = rp.referral'
            )
            ->where('r = :referral')
            ->setParameter('referral', $referral);

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
                ->andWhere('rp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array $referralIds
     * @return mixed
     */
    public function getByReferralIds(Space $space = null, array $entityGrants = null, array $referralIds)
    {
        $qb = $this->createQueryBuilder('rp');

        $qb
            ->select('
                    rp.id as id,
                    r.id as rId,
                    rp.primary as primary,
                    rp.type as type,
                    rp.number as number
            ')
            ->innerJoin(
                Referral::class,
                'r',
                Join::WITH,
                'r = rp.referral'
            )
            ->where('r.id IN (:referralIds)')
            ->setParameter('referralIds', $referralIds);

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
                ->andWhere('rp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('rp.id')
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
            ->createQueryBuilder('rp')
            ->select('rp.number');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('rp.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('rp.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('rp.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Referral::class,
                    'r',
                    Join::WITH,
                    'r = rp.referral'
                )
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
                ->andWhere('rp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
