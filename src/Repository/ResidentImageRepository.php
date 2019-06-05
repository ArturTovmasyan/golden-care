<?php

namespace App\Repository;

use App\Entity\Resident;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class ResidentImageRepository
 * @package App\Repository
 */
class ResidentImageRepository extends EntityRepository
{
    /**
     * @param $id
     * @return mixed
     */
    public function getBy($id)
    {
        $qb = $this
            ->createQueryBuilder('ri')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = ri.resident'
            )
            ->where('r.id = :id')
            ->setParameter('id', $id);

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }
}