<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Diagnosis;
use App\Entity\ResidentDiagnosis;
use App\Entity\Resident;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentDiagnosisRepository
 * @package App\Repository
 */
class ResidentDiagnosisRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(ResidentDiagnosis::class, 'rd')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rd.resident'
            )
            ->innerJoin(
                Diagnosis::class,
                'd',
                Join::WITH,
                'd = rd.diagnosis'
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
                ->andWhere('rd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('rd.id');
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
            ->createQueryBuilder('rd')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rd.resident'
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
                ->andWhere('rd.id IN (:grantIds)')
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
            ->createQueryBuilder('rd')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rd.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('rd.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rd.id IN (:grantIds)')
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
            ->createQueryBuilder('rd')
            ->where('rd.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rd.resident'
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
                ->andWhere('rd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('rd.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(Space $space = null, array $entityGrants = null, array $residentIds)
    {
        $qb = $this->createQueryBuilder('rd');

        $qb
            ->select('
                    r.id as residentId,
                    rd.id as diagnosisId,
                    rd.type as diagnosisType,
                    d.title as diagnosisTitle
            ')
            ->innerJoin(
                Diagnosis::class,
                'd',
                Join::WITH,
                'rd.diagnosis = d'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'rd.resident = r'
            )
            ->where($qb->expr()->in('r.id', $residentIds));

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
                ->andWhere('rd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('rd.type', 'ASC')
            ->addOrderBy('d.title')
            ->groupBy('rd.id')
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
            ->createQueryBuilder('rd')
            ->innerJoin(
                Diagnosis::class,
                'd',
                Join::WITH,
                'rd.diagnosis = d'
            )
            ->select('d.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('rd.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('rd.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('rd.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rd.resident'
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
                ->andWhere('rd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}