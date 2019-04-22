<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\CityStateZip;
use App\Entity\Relationship;
use App\Entity\Resident;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePerson;
use App\Entity\ResponsiblePersonRole;
use App\Entity\Salutation;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentResponsiblePersonRepository
 * @package App\Repository
 */
class ResidentResponsiblePersonRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(ResidentResponsiblePerson::class, 'rrp')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rrp.resident'
            )
            ->innerJoin(
                ResponsiblePerson::class,
                'rp',
                Join::WITH,
                'rp = rrp.responsiblePerson'
            )
            ->innerJoin(
                Relationship::class,
                'rel',
                Join::WITH,
                'rel = rrp.relationship'
            )
            ->innerJoin(
                ResponsiblePersonRole::class,
                'role',
                Join::WITH,
                'role = rrp.role'
            )
            ->innerJoin(
                Salutation::class,
                'rps',
                Join::WITH,
                'rps = rp.salutation'
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
                ->andWhere('rrp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('rrp.id');
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
            ->createQueryBuilder('rrp')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rrp.resident'
            )
            ->innerJoin(
                ResponsiblePerson::class,
                'rp',
                Join::WITH,
                'rp = rrp.responsiblePerson'
            )
            ->leftJoin(
                Salutation::class,
                'rps',
                Join::WITH,
                'rps = rp.salutation'
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
                ->andWhere('rrp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $qb
            ->addOrderBy('rrp.sortOrder', 'ASC')
            ->addOrderBy("CONCAT(
            CASE WHEN rps IS NOT NULL THEN CONCAT(rps.title, ' ') ELSE '' END, 
            rp.firstName, ' ', rp.lastName)", 'ASC')
        ;

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
            ->createQueryBuilder('rrp')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rrp.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('rrp.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rrp.id IN (:grantIds)')
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
            ->createQueryBuilder('rrp')
            ->where('rrp.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rrp.resident'
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
                ->andWhere('rrp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('rrp.id')
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
        $qb = $this->createQueryBuilder('rrp');

        $qb
            ->select('
                    rrp.id as id,
                    r.id as residentId,
                    rp.firstName as firstName,
                    rp.lastName as lastName,
                    rp.address_1 as address,
                    csz.stateFull as state,
                    csz.zipMain as zip,
                    csz.city as city,
                    rp.id as rpId,
                    rel.title as relationshipTitle
            ')
            ->innerJoin(
                ResponsiblePerson::class,
                'rp',
                Join::WITH,
                'rrp.responsiblePerson = rp'
            )
            ->innerJoin(
                Relationship::class,
                'rel',
                Join::WITH,
                'rrp.relationship = rel'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'rrp.resident = r'
            )
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'rp.csz = csz'
            )
            ->where('r.id IN (:residentIds)')
            ->setParameter('residentIds', $residentIds);

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
                ->andWhere('rrp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('rrp.id')
            ->orderBy('rrp.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array $residentIds
     * @return mixed
     */
    public function getResponsiblePersonByResidentIds(Space $space = null, array $entityGrants = null, array $residentIds)
    {
        $qb = $this->createQueryBuilder('rrp');

        $qb
            ->innerJoin(
                ResponsiblePerson::class,
                'rp',
                Join::WITH,
                'rrp.responsiblePerson = rp'
            )
            ->innerJoin(
                Relationship::class,
                'rel',
                Join::WITH,
                'rrp.relationship = rel'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'rrp.resident = r'
            )
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'rp.csz = csz'
            )
            ->leftJoin('rrp.roles','rs')
            ->where('r.id IN (:residentIds)')
            ->setParameter('residentIds', $residentIds);

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
                ->andWhere('rrp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('rrp.id')
            ->orderBy('rrp.sortOrder', 'ASC')
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
            ->createQueryBuilder('rrp')
            ->innerJoin(
                ResponsiblePerson::class,
                'rp',
                Join::WITH,
                'rrp.responsiblePerson = rp'
            )
            ->select("CONCAT(rp.firstName, ' ', rp.lastName) as fullName");

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('rrp.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('rrp.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('rrp.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rrp.resident'
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
                ->andWhere('rrp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}