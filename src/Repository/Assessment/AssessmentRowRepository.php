<?php

namespace App\Repository\Assessment;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Assessment\Assessment;
use App\Entity\Assessment\Row;
use App\Entity\Resident;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class AssessmentRowRepository
 * @package App\Repository\Assessment
 */
class AssessmentRowRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $assessment
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, $assessment)
    {
        $qb = $this
            ->createQueryBuilder('ar')
            ->innerJoin(
                Assessment::class,
                'a',
                Join::WITH,
                'a = ar.assessment'
            )
            ->where('a = :assessment')
            ->setParameter('assessment', $assessment);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = a.resident'
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
                ->andWhere('ar.id IN (:grantIds)')
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
            ->createQueryBuilder('ar')
            ->innerJoin(
                Row::class,
                'row',
                Join::WITH,
                'row = ar.row'
            )
            ->select('row.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('ar.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('ar.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('ar.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Assessment::class,
                    'a',
                    Join::WITH,
                    'a = ar.assessment'
                )
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = a.resident'
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
                ->andWhere('ar.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}