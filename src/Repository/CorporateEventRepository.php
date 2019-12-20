<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\EventDefinition;
use App\Entity\CorporateEvent;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CorporateEventRepository
 * @package App\Repository
 */
class CorporateEventRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $facilityEntityGrants
     * @param QueryBuilder $queryBuilder
     * @param array|null $userRoleIds
     */
    public function search(Space $space = null, array $entityGrants = null, $facilityEntityGrants, QueryBuilder $queryBuilder, array $userRoleIds = null) : void
    {
        $queryBuilder
            ->from(CorporateEvent::class, 'ce')
            ->addSelect("GROUP_CONCAT(DISTINCT f.name SEPARATOR ', ') AS facilities")
            ->addSelect("GROUP_CONCAT(DISTINCT r.name SEPARATOR ', ') AS roles")
            ->innerJoin(
                EventDefinition::class,
                'ed',
                Join::WITH,
                'ed = ce.definition'
            )
            ->leftJoin('ce.facilities', 'f')
            ->leftJoin('ce.roles', 'r')
        ;

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ed.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('ce.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $queryBuilder
                ->andWhere('f.id IN (:facilityGrantIds) OR f.id IS NULL')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        if ($userRoleIds !== null) {
            $queryBuilder
                ->andWhere('r.id IN (:userRoleIds) OR r.id IS NULL')
                ->setParameter('userRoleIds', $userRoleIds);
        }

        $queryBuilder
            ->orderBy('ce.start', 'DESC')
            ->groupBy('ce.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $facilityEntityGrants
     * @param array|null $userRoleIds
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null, $facilityEntityGrants, array $userRoleIds = null)
    {
        $qb = $this
            ->createQueryBuilder('ce')
            ->innerJoin(
                EventDefinition::class,
                'ed',
                Join::WITH,
                'ed = ce.definition'
            )
            ->leftJoin('ce.facilities', 'f')
            ->leftJoin('ce.roles', 'r');

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ed.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ce.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds) OR f.id IS NULL')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        if ($userRoleIds !== null) {
            $qb
                ->andWhere('r.id IN (:userRoleIds) OR r.id IS NULL')
                ->setParameter('userRoleIds', $userRoleIds);
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
            ->createQueryBuilder('ce')
            ->innerJoin(
                EventDefinition::class,
                'ed',
                Join::WITH,
                'ed = ce.definition'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ed.space'
            )
            ->where('ce.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ce.id IN (:grantIds)')
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
            ->createQueryBuilder('ce')
            ->where('ce.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    EventDefinition::class,
                    'ed',
                    Join::WITH,
                    'ed = ce.definition'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ed.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ce.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('ce.id')
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
            ->createQueryBuilder('ce')
            ->innerJoin(
                EventDefinition::class,
                'ed',
                Join::WITH,
                'ed = ce.definition'
            )
            ->select('ce.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('ce.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('ce.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('ce.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ed.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ce.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    ///////////// For Calendar /////////////////////////////////////////////////////////////////////////////////////////
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $facilityEntityGrants
     * @param array|null $userRoleIds
     * @param null $dateFrom
     * @param null $dateTo
     * @return mixed
     */
    public function getCorporateCalendarData(Space $space = null, array $entityGrants = null, $facilityEntityGrants, array $userRoleIds = null, $dateFrom = null, $dateTo = null)
    {
        $qb = $this->createQueryBuilder('ce');

        $qb
            ->select(
                'ce.id AS id',
                'ce.title AS title',
                'ce.start AS start',
                'ce.end AS end',
                'ce.done AS done',
                'ce.notes AS notes'
            )
            ->innerJoin(
                EventDefinition::class,
                'ed',
                Join::WITH,
                'ed = ce.definition'
            )
            ->leftJoin('ce.facilities', 'f')
            ->leftJoin('ce.roles', 'r');

        if ($dateFrom !== null) {
            $qb
                ->andWhere('ce.start >= :start')
                ->andWhere('ce.end IS NULL OR ce.end >= :start')
                ->setParameter('start', $dateFrom);
        }

        if ($dateTo !== null) {
            $qb
                ->andWhere('ce.start <= :end')
                ->setParameter('end', $dateTo);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ed.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ce.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds) OR f.id IS NULL')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        if ($userRoleIds !== null) {
            $qb
                ->andWhere('r.id IN (:userRoleIds) OR r.id IS NULL')
                ->setParameter('userRoleIds', $userRoleIds);
        }

        return $qb
            ->groupBy('ce.id')
            ->getQuery()
            ->getResult();
    }
    ///////////// End For Calendar /////////////////////////////////////////////////////////////////////////////////////
}