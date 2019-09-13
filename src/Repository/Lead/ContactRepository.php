<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Lead\Organization;
use App\Entity\Lead\Contact;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ContactRepository
 * @package App\Repository\Lead
 */
class ContactRepository extends EntityRepository  implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     * @param null $userId
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder, $userId = null) : void
    {
        $queryBuilder
            ->from(Contact::class, 'c')
            ->leftJoin(
                Organization::class,
                'o',
                Join::WITH,
                'o = c.organization'
            )
            ->innerJoin(
                User::class,
                'u',
                Join::WITH,
                'u = c.createdBy'
            );

        if ($userId !== null) {
            $queryBuilder
                ->andWhere('u.id = :userId')
                ->setParameter('userId', $userId);
        }

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = c.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('c.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('c.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param null $userId
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null, $userId = null)
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->innerJoin(
                User::class,
                'u',
                Join::WITH,
                'u = c.createdBy'
            );

        if ($userId !== null) {
            $qb
                ->andWhere('u.id = :userId')
                ->setParameter('userId', $userId);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = c.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('c.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $organizationId
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, $organizationId)
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->leftJoin(
                Organization::class,
                'o',
                Join::WITH,
                'o = c.organization'
            )
            ->where('o.id = :organizationId')
            ->setParameter('organizationId', $organizationId);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = c.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('c.id IN (:grantIds)')
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
            ->createQueryBuilder('c')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = c.space'
            )
            ->where('c.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('c.id IN (:grantIds)')
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
            ->createQueryBuilder('c')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = c.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('c.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('c.id')
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
            ->createQueryBuilder('c')
            ->leftJoin(
                Organization::class,
                'o',
                Join::WITH,
                'o = c.organization'
            )
            ->select("CONCAT(c.firstName, ' ', c.lastName) as fullName");

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('c.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('c.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('c.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = c.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('c.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getContactList(Space $space = null, array $entityGrants = null, $startDate, $endDate)
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->select(
                'c.id as id',
                'c.firstName as firstName',
                'c.lastName as lastName',
                'o.name as orgTitle',
                'c.emails as emails',
                'c.notes as notes'
            )
            ->leftJoin(
                Organization::class,
                'o',
                Join::WITH,
                'o = c.organization'
            )
            ->where('c.createdAt >= :startDate')->setParameter('startDate', $startDate)
            ->andWhere('c.createdAt < :endDate')->setParameter('endDate', $endDate);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = c.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('c.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
