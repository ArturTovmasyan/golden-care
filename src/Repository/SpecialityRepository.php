<?php

namespace App\Repository;

use App\Entity\Space;
use App\Entity\Speciality;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SpecialityRepository
 * @package App\Repository
 */
class SpecialityRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Speciality::class, 's')
            ->leftJoin(
                Space::class,
                'sp',
                Join::WITH,
                'sp = s.space'
            )
            ->groupBy('s.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('s');

        return $qb->where($qb->expr()->in('s.id', $ids))
            ->groupBy('s.id')
            ->getQuery()
            ->getResult();
    }
}