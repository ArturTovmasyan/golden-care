<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\MedicalHistoryCondition;
use App\Entity\ResidentMedicalHistoryCondition;
use App\Entity\Resident;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentMedicalHistoryConditionRepository
 * @package App\Repository
 */
class ResidentMedicalHistoryConditionRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(ResidentMedicalHistoryCondition::class, 'rmhc')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rmhc.resident'
            )
            ->innerJoin(
                MedicalHistoryCondition::class,
                'mhc',
                Join::WITH,
                'mhc = rmhc.condition'
            );

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('rmhc.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('rmhc.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('rmhc')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rmhc.resident'
            )
            ->where('r.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rmhc.id IN (:grantIds)')
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
            ->createQueryBuilder('rmhc')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rmhc.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('rmhc.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rmhc.id IN (:grantIds)')
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
            ->createQueryBuilder('rmhc')
            ->where('rmhc.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rmhc.resident'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rmhc.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('rmhc.id')
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
            ->createQueryBuilder('rmhc')
            ->innerJoin(
                MedicalHistoryCondition::class,
                'mhc',
                Join::WITH,
                'mhc = rmhc.condition'
            )
            ->select('mhc.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('rmhc.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('rmhc.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('rmhc.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rmhc.resident'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rmhc.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}