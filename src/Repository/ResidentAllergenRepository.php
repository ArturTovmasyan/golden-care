<?php

namespace App\Repository;

use App\Entity\Allergen;
use App\Entity\ResidentAllergen;
use App\Entity\Resident;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentAllergenRepository
 * @package App\Repository
 */
class ResidentAllergenRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(ResidentAllergen::class, 'ra')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = ra.resident'
            )
            ->innerJoin(
                Allergen::class,
                'a',
                Join::WITH,
                'a = ra.allergen'
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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('ra.id');
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
            ->createQueryBuilder('ra')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = ra.resident'
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
                ->andWhere('ra.id IN (:grantIds)')
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
            ->createQueryBuilder('ra')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = ra.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('ra.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ra.id IN (:grantIds)')
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
            ->createQueryBuilder('ra')
            ->where('ra.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = ra.resident'
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
                ->andWhere('ra.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('ra.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(Space $space = null, array $residentIds)
    {
        $qb = $this->createQueryBuilder('ra');

        $qb
            ->select('
                    a.id as id,
                    a.title as title,
                    a.description as description,
                    r.id as residentId
            ')
            ->innerJoin(
                Allergen::class,
                'a',
                Join::WITH,
                'ra.allergen = a'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'ra.resident = r'
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

        return $qb
            ->orderBy('a.title')
            ->groupBy('ra.id')
            ->getQuery()
            ->getResult();
    }
}