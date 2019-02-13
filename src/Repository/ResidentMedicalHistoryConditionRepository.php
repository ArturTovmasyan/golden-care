<?php

namespace App\Repository;

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
class ResidentMedicalHistoryConditionRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, QueryBuilder $queryBuilder)
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

        $queryBuilder
            ->groupBy('rmhc.id');
    }

    /**
     * @param Space|null $space
     * @param $id
     * @return mixed
     */
    public function getBy(Space $space = null, $id)
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

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param $id
     * @return mixed
     */
    public function getOne(Space $space = null, $id)
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

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param $ids
     * @return mixed
     */
    public function findByIds(Space $space = null, $ids)
    {
        $qb = $this->createQueryBuilder('rmhc');

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

        return $qb->where($qb->expr()->in('rmhc.id', $ids))
            ->groupBy('rmhc.id')
            ->getQuery()
            ->getResult();
    }
}