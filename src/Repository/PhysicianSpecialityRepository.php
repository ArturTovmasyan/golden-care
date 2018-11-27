<?php

namespace App\Repository;

use App\Entity\Physician;
use App\Entity\PhysicianSpeciality;
use App\Entity\ResidentAllergen;
use App\Entity\Speciality;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PhysicianSpecialityRepository
 * @package App\Repository
 */
class PhysicianSpecialityRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(PhysicianSpeciality::class, 'ps')
            ->leftJoin(
                Physician::class,
                'p',
                Join::WITH,
                'p = ps.physician'
            )
            ->leftJoin(
                Speciality::class,
                's',
                Join::WITH,
                's = ps.speciality'
            )
            ->groupBy('ps.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('ps');

        return $qb->where($qb->expr()->in('ps.id', $ids))
            ->groupBy('ps.id')
            ->getQuery()
            ->getResult();
    }
}