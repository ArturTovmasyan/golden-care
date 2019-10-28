<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Document;
use App\Entity\DocumentCategory;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DocumentRepository
 * @package App\Repository
 */
class DocumentRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $facilityEntityGrants
     * @param QueryBuilder $queryBuilder
     * @param array|null $userRoleIds
     * @param null $categoryId
     */
    public function search(Space $space = null, array $entityGrants = null, $facilityEntityGrants, QueryBuilder $queryBuilder, array $userRoleIds = null, $categoryId = null) : void
    {
        $queryBuilder
            ->from(Document::class, 'd')
            ->join('d.facilities', 'f')
            ->leftJoin('d.roles', 'r')
            ->innerJoin(
                DocumentCategory::class,
                'dc',
                Join::WITH,
                'dc = d.category'
            )
            ->leftJoin(
                User::class,
                'u',
                Join::WITH,
                'u = d.updatedBy'
            );

        if ($userRoleIds !== null) {
            $queryBuilder
                ->andWhere('r.id IN (:userRoleIds)')
                ->setParameter('userRoleIds', $userRoleIds);
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
                    's = dc.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('d.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $queryBuilder
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        $queryBuilder
            ->groupBy('d.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $facilityEntityGrants
     * @param array|null $userRoleIds
     * @param null $categoryId
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null, $facilityEntityGrants, array $userRoleIds = null, $categoryId = null)
    {
        $qb = $this
            ->createQueryBuilder('d')
            ->join('d.facilities', 'f')
            ->leftJoin('d.roles', 'r')
            ->innerJoin(
                DocumentCategory::class,
                'dc',
                Join::WITH,
                'dc = d.category'
            );

        if ($userRoleIds !== null) {
            $qb
                ->andWhere('r.id IN (:userRoleIds)')
                ->setParameter('userRoleIds', $userRoleIds);
        }

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
                    's = dc.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('d.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($facilityEntityGrants !== null) {
            $qb
                ->andWhere('f.id IN (:facilityGrantIds)')
                ->setParameter('facilityGrantIds', $facilityEntityGrants);
        }

        $qb
            ->addOrderBy('d.title', 'ASC');

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
            ->createQueryBuilder('d')
            ->innerJoin(
                DocumentCategory::class,
                'dc',
                Join::WITH,
                'dc = d.category'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = dc.space'
            )
            ->where('d.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('d.id IN (:grantIds)')
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
            ->createQueryBuilder('d')
            ->where('d.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    DocumentCategory::class,
                    'dc',
                    Join::WITH,
                    'dc = d.category'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = dc.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('d.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('d.id')
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
            ->createQueryBuilder('d')
            ->select('d.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('d.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('d.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('d.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    DocumentCategory::class,
                    'dc',
                    Join::WITH,
                    'dc = d.category'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = dc.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('d.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}