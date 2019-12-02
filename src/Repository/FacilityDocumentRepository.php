<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\DocumentCategory;
use App\Entity\Facility;
use App\Entity\FacilityDocument;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityDocumentRepository
 * @package App\Repository
 */
class FacilityDocumentRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @param QueryBuilder $queryBuilder
     * @param null $facilityId
     * @param null $categoryId
     */
    public function search(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null, QueryBuilder $queryBuilder, $facilityId = null, $categoryId = null) : void
    {
        $queryBuilder
            ->from(FacilityDocument::class, 'fd')
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = fd.facility'
            )
            ->innerJoin(
                DocumentCategory::class,
                'dc',
                Join::WITH,
                'dc = fd.category'
            )
            ->innerJoin(
                User::class,
                'u',
                Join::WITH,
                'u = fd.createdBy'
            );

        if ($facilityId !== null) {
            $queryBuilder
                ->where('f.id = :facilityId')
                ->setParameter('facilityId', $facilityId);
        }

        if ($categoryId !== null) {
            $queryBuilder
                ->andWhere('dc.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = f.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('fd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $queryBuilder
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        $queryBuilder
            ->orderBy('fd.createdAt', 'DESC')
            ->groupBy('fd.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @param $id
     * @param null $categoryId
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null, $id, $categoryId = null)
    {
        $qb = $this
            ->createQueryBuilder('fd')
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = fd.facility'
            )
            ->innerJoin(
                DocumentCategory::class,
                'dc',
                Join::WITH,
                'dc = fd.category'
            )
            ->where('f.id = :id')
            ->setParameter('id', $id);

        if ($categoryId !== null) {
            $qb
                ->andWhere('dc.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = f.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        return $qb
            ->orderBy('fd.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @param $id
     * @return mixed
     */
    public function getOne(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('fd')
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = fd.facility'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = f.space'
            )
            ->where('fd.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array|null $facilityEntityGrants
     * @param $ids
     * @return mixed
     */
    public function findByIds(Space $space = null, array $entityGrants = null, array $facilityEntityGrants = null, $ids)
    {
        $qb = $this
            ->createQueryBuilder('fd')
            ->where('fd.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Facility::class,
                    'f',
                    Join::WITH,
                    'f = fd.facility'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = f.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        return $qb->groupBy('fd.id')
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
            ->createQueryBuilder('fd')
            ->select('fd.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('fd.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('fd.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('fd.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Facility::class,
                    'f',
                    Join::WITH,
                    'f = fd.facility'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = f.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('fd.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}