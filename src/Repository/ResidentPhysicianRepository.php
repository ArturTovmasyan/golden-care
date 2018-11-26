<?php

namespace App\Repository;

use App\Entity\Physician;
use App\Entity\Resident;
use App\Entity\ResidentPhysician;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentPhysicianRepository
 * @package App\Repository
 */
class ResidentPhysicianRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param bool $residentId
     */
    public function search(QueryBuilder $queryBuilder, $residentId = false)
    {
        $queryBuilder
            ->from(ResidentPhysician::class, 'rp')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rp.resident'
            )
            ->leftJoin(
                Physician::class,
                'p',
                Join::WITH,
                'p = rp.physician'
            )
            ->groupBy('rp.id');

        if ($residentId) {
            $queryBuilder
                ->where('rp.resident = :residentId')
                ->setParameter('residentId', $residentId);
        }
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('rp');

        return $qb->where($qb->expr()->in('rp.id', $ids))
            ->groupBy('rp.id')
            ->getQuery()
            ->getResult();
    }
}