<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class UserRepository
 * @package App\Repository
 */
class UserRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->from(User::class, 'u')
            ->addSelect("GROUP_CONCAT(r.name SEPARATOR ', ') AS roles")
            ->addSelect(
                "(SELECT
                          CASE WHEN JSON_CONTAINS(JSON_ARRAYAGG(r.id), '0')=1
                                OR JSON_CONTAINS(JSON_ARRAYAGG(r.id), '1')=1 THEN
                          'All' ELSE GROUP_CONCAT(f.name SEPARATOR ', ') END
                        FROM
                          App\\Entity\\Facility f
                        WHERE JSON_CONTAINS(
                            JSON_EXTRACT(
                              u.grants,
                              '$.\"persistence-facility\"'
                            ),
                            CAST(f.id AS JSON)
                          ) = 1) AS facilities")
            ->innerJoin('u.roles', 'r')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = u.space'
            );

        if ($space !== null) {
            $queryBuilder
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('u.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('u.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null)
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = u.space'
            );

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('u.id IN (:grantIds)')
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
            ->createQueryBuilder('u')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = u.space'
            )
            ->where('u.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('u.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $username
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUserByUsername($username)
    {
        return $this->createQueryBuilder('u')
            ->where('(u.username = :username OR u.email = :email) AND u.enabled = true AND u.completed = true')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param array $criteria
     * @return array|mixed
     */
    public function getUserSpaceAndOwnerCriteria(array $criteria)
    {
        if (1 == $criteria['owner']) {
            return $this->createQueryBuilder('u')
                ->andWhere('u.space = :space')
                ->setParameter('space', $criteria['space'])
                ->andWhere('u.owner = :owner')
                ->setParameter('owner', $criteria['owner'])
                ->getQuery()
                ->getResult();
        }

        return [];
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
            ->createQueryBuilder('u')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = u.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('u.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('u.id')
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
            ->createQueryBuilder('u')
            ->select("CONCAT(u.firstName, ' ', u.lastName) as fullName");

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('u.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('u.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('u.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = u.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('u.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $date
     * @param $userId
     * @return mixed
     */
    public function mobileList(Space $space = null, array $entityGrants = null, $date, $userId)
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->select(
                'u.id AS id',
                'u.firstName AS first_name',
                'u.lastName AS last_name',
                'u.username AS user_name',
                'u.email AS email',
                'u.enabled AS enabled',
                'u.owner AS owner',
                'u.lastActivityAt AS last_activity_at',
                'u.updatedAt AS updated_at',
                'u.licenseAccepted AS license_accepted',
                's.name AS space'
            )
            ->addSelect("GROUP_CONCAT(r.name SEPARATOR ', ') AS roles")
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = u.space'
            )
            ->innerJoin('u.roles', 'r')
            ->where('u.updatedAt > :date')
            ->andWhere('u.id = :userId')
            ->setParameter('date', $date)
            ->setParameter('userId', $userId);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('u.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    ///////////// For Facility Dashboard ///////////////////////////////////////////////////////////////////////////////

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $ids
     * @return mixed
     */
    public function getFacilityIdsByIds(Space $space = null, array $entityGrants = null, $ids)
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->select('u.id as id')
            ->addSelect(
                "(SELECT GROUP_CONCAT(DISTINCT f.id SEPARATOR ',')
                        FROM
                          App\\Entity\\Facility f
                        WHERE JSON_CONTAINS(
                            JSON_EXTRACT(
                              u.grants,
                              '$.\"persistence-facility\"'
                            ),
                            CAST(f.id AS JSON)
                          ) = 1) AS facilityIds")
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = u.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('u.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }
    ///////////////// End For Facility Dashboard ///////////////////////////////////////////////////////////////////////

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $roleIds
     * @return mixed
     */
    public function getEnabledUserFacilityIdsByRoles(Space $space = null, array $entityGrants = null, $roleIds)
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->select('
                u.id as id,
                u.email as email
            ')
            ->addSelect(
                "(SELECT GROUP_CONCAT(DISTINCT f.id SEPARATOR ',')
                        FROM
                          App\\Entity\\Facility f
                        WHERE JSON_CONTAINS(
                            JSON_EXTRACT(
                              u.grants,
                              '$.\"persistence-facility\"'
                            ),
                            CAST(f.id AS JSON)
                          ) = 1) AS facilityIds")
            ->innerJoin('u.roles', 'r')
            ->where('u.enabled=1')
            ->andWhere('r.id IN (:roleIds)')
            ->setParameter('roleIds', $roleIds);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = u.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('u.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }
}
