<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Facility;
use App\Entity\Lead\Temperature;
use App\Entity\Lead\Lead;
use App\Entity\Lead\LeadTemperature;
use App\Entity\Space;
use App\Entity\User;
use App\Model\Lead\State;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class LeadTemperatureRepository
 * @package App\Repository\Lead
 */
class LeadTemperatureRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->from(LeadTemperature::class, 'lt')
            ->innerJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = lt.lead'
            )
            ->innerJoin(
                Temperature::class,
                't',
                Join::WITH,
                't = lt.temperature'
            )
            ->leftJoin(
                User::class,
                'u',
                Join::WITH,
                'u = lt.createdBy'
            );

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = t.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('lt.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('lt.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null)
    {
        $qb = $this
            ->createQueryBuilder('lt')
            ->innerJoin(
                Temperature::class,
                't',
                Join::WITH,
                't = lt.temperature'
            );

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = t.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('lt.id IN (:grantIds)')
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
            ->createQueryBuilder('lt')
            ->innerJoin(
                Temperature::class,
                't',
                Join::WITH,
                't = lt.temperature'
            )
            ->where('lt.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = t.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('lt.id IN (:grantIds)')
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
            ->createQueryBuilder('lt')
            ->where('lt.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Temperature::class,
                    't',
                    Join::WITH,
                    't = lt.temperature'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = t.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('lt.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('lt.id')
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
            ->createQueryBuilder('lt')
            ->innerJoin(
                Temperature::class,
                't',
                Join::WITH,
                't = lt.temperature'
            )
            ->select('t.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('lt.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('lt.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('lt.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = t.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('lt.id IN (:grantIds)')
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
    public function getLastAction(Space $space = null, array $entityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('lt')
            ->join('lt.lead', 'l')
            ->where('l.id=:id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Temperature::class,
                    't',
                    Join::WITH,
                    't = lt.temperature'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = t.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('lt.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('lt.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    ///////////// For Facility Dashboard ///////////////////////////////////////////////////////////////////////////////

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $startDate
     * @param $endDate
     * @param null $facilityId
     * @return mixed
     */
    public function getHotLeadsForFacilityDashboard(Space $space = null, array $entityGrants = null, $startDate, $endDate, $facilityId = null)
    {
        /** @var TemperatureRepository $temperatureRepo */
        $temperatureRepo = $this
            ->getEntityManager()
            ->getRepository(Temperature::class);

        /** @var Temperature $hot */
        $hot = $temperatureRepo
            ->getLast($space, null);

        $hotId = 0;
        if ($hot !== null) {
            $hotId = $hot->getId();
        }

        $qb = $this
            ->createQueryBuilder('lt')
            ->select(
                'lt.id as id',
                'l.id as leadId',
                'f.id as typeId'
            )
            ->innerJoin(
                Temperature::class,
                't',
                Join::WITH,
                't = lt.temperature'
            )
            ->innerJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = lt.lead'
            )
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = l.primaryFacility'
            )
            ->where('lt.createdAt >= :startDate AND lt.createdAt <= :endDate AND l.state = :state')
            ->andWhere('t.id = :hotId')
            ->setParameter('state', State::TYPE_OPEN)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('hotId', $hotId);

        if ($facilityId !== null) {
            $qb
                ->andWhere('f.id = :facilityId')
                ->setParameter('facilityId', $facilityId);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = t.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('lt.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('l.id')
            ->getQuery()
            ->getResult();
    }
    ///////////////// End For Facility Dashboard ///////////////////////////////////////////////////////////////////////
}
