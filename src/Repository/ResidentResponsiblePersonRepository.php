<?php

namespace App\Repository;

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
}