<?php

namespace App\Repository;

use App\Entity\MedicationFormFactor;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class MedicationFormFactorRepository
 * @package App\Repository
 */
class MedicationFormFactorRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(MedicationFormFactor::class, 'mff')
            ->groupBy('mff.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('mff');

        return $qb->where($qb->expr()->in('mff.id', $ids))
            ->groupBy('mff.id')
            ->getQuery()
            ->getResult();
    }
}
