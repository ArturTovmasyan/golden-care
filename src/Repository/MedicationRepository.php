<?php

namespace App\Repository;

use App\Entity\Medication;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class MedicationRepository
 * @package App\Repository
 */
class MedicationRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Medication::class, 'm')
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = m.space'
            )
            ->groupBy('m.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('m');

        return $qb->where($qb->expr()->in('m.id', $ids))
            ->groupBy('m.id')
            ->getQuery()
            ->getResult();
    }
}
