<?php

namespace App\Repository;

use App\Entity\Medication;
use App\Entity\ResidentMedicationAllergy;
use App\Entity\Resident;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentMedicationAllergyRepository
 * @package App\Repository
 */
class ResidentMedicationAllergyRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentMedicationAllergy::class, 'rma')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rma.resident'
            )
            ->leftJoin(
                Medication::class,
                'm',
                Join::WITH,
                'm = rma.medication'
            )
            ->groupBy('rma.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('rma');

        return $qb->where($qb->expr()->in('rma.id', $ids))
            ->groupBy('rma.id')
            ->getQuery()
            ->getResult();
    }
}