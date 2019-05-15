<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Lead\Organization;
use App\Entity\Lead\ReferrerType;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class OrganizationPhoneRepository
 * @package App\Repository
 */
class OrganizationPhoneRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param Organization $organization
     * @return mixed
     *
     */
    public function getBy(Space $space = null, array $entityGrants = null, Organization $organization)
    {
        $qb = $this
            ->createQueryBuilder('op')
            ->innerJoin(
                Organization::class,
                'o',
                Join::WITH,
                'o = op.organization'
            )
            ->where('o = :organization')
            ->setParameter('organization', $organization);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ReferrerType::class,
                    'c',
                    Join::WITH,
                    'c = o.category'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = c.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('op.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
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
            ->createQueryBuilder('op')
            ->select('op.number');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('op.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('op.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('op.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Organization::class,
                    'o',
                    Join::WITH,
                    'o = op.organization'
                )
                ->innerJoin(
                    ReferrerType::class,
                    'c',
                    Join::WITH,
                    'c = o.category'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = c.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('op.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
