<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Physician;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class PhysicianPhoneRepository
 * @package App\Repository
 */
class PhysicianPhoneRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param Physician $physician
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, Physician $physician)
    {
        $qb = $this
            ->createQueryBuilder('pp')
            ->innerJoin(
                Physician::class,
                'p',
                Join::WITH,
                'p = pp.physician'
            )
            ->where('p = :physician')
            ->setParameter('physician', $physician);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = p.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('pp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array $physicianIds
     * @return mixed
     */
    public function getByPhysicianIds(Space $space = null, array $entityGrants = null, array $physicianIds)
    {
        $qb = $this->createQueryBuilder('pp');

        $qb
            ->select('
                    pp.id as id,
                    p.id as pId,
                    pp.primary as primary,
                    pp.type as type,
                    pp.extension as extension,
                    pp.number as number
            ')
            ->innerJoin(
                Physician::class,
                'p',
                Join::WITH,
                'pp.physician = p'
            )
            ->where('p.id IN (:physicianIds)')
            ->setParameter('physicianIds', $physicianIds);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = p.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('pp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('pp.id')
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
            ->createQueryBuilder('pp')
            ->select('pp.number');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('pp.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('pp.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('pp.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Physician::class,
                    'p',
                    Join::WITH,
                    'pp.physician = p'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = p.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('pp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
