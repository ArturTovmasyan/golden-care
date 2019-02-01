<?php

namespace App\Repository;

use App\Entity\CityStateZip;
use App\Entity\Relationship;
use App\Entity\Resident;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePerson;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentResponsiblePersonRepository
 * @package App\Repository
 */
class ResidentResponsiblePersonRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param bool $residentId
     */
    public function search(QueryBuilder $queryBuilder, $residentId = false)
    {
        $queryBuilder
            ->from(ResidentResponsiblePerson::class, 'rrp')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rrp.resident'
            )
            ->leftJoin(
                ResponsiblePerson::class,
                'rp',
                Join::WITH,
                'rp = rrp.responsiblePerson'
            )
            ->leftJoin(
                Relationship::class,
                'rel',
                Join::WITH,
                'rel = rrp.relationship'
            )
            ->groupBy('rrp.id');

        if ($residentId) {
            $queryBuilder
                ->where('rrp.resident = :residentId')
                ->setParameter('residentId', $residentId);
        }
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('rrp');

        return $qb->where($qb->expr()->in('rrp.id', $ids))
            ->groupBy('rrp.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(array $residentIds)
    {
        $qb = $this->createQueryBuilder('rrp');

        return $qb
            ->select('
                    rrp.id as id,
                    r.id as residentId,
                    rp.firstName as firstName,
                    rp.lastName as lastName,
                    rp.address_1 as address,
                    rp.financially as financially,
                    rp.emergency as emergency,
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

            ->where($qb->expr()->in('r.id', $residentIds))
            ->groupBy('rrp.id')
            ->getQuery()
            ->getResult();
    }
}