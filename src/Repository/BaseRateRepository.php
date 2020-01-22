<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\CareLevel;
use App\Entity\FacilityRoomType;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class BaseRateRepository
 * @package App\Repository
 */
class BaseRateRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $roomType
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, $roomType)
    {
        $qb = $this
            ->createQueryBuilder('br')
            ->innerJoin(
                FacilityRoomType::class,
                'frt',
                Join::WITH,
                'frt = br.roomType'
            )
            ->where('frt = :roomType')
            ->setParameter('roomType', $roomType);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    CareLevel::class,
                    'cl',
                    Join::WITH,
                    'cl = br.careLevel'
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
                ->andWhere('br.id IN (:grantIds)')
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
            ->createQueryBuilder('br')
            ->where('br.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    CareLevel::class,
                    'cl',
                    Join::WITH,
                    'cl = br.careLevel'
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
                ->andWhere('br.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('br.id')
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
            ->createQueryBuilder('br')
            ->innerJoin(
                CareLevel::class,
                'cl',
                Join::WITH,
                'cl = br.careLevel'
            )
            ->select('br.amount as amount');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('br.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('br.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('br.id IN (:array)')
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
                ->andWhere('br.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}