<?php

namespace App\Repository;

use App\Entity\Medication;
use App\Entity\MedicationFormFactor;
use App\Entity\Physician;
use App\Entity\ResidentMedication;
use App\Entity\Resident;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentMedicationRepository
 * @package App\Repository
 */
class ResidentMedicationRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentMedication::class, 'rm')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rm.resident'
            )
            ->leftJoin(
                Physician::class,
                'p',
                Join::WITH,
                'p = rm.physician'
            )
            ->leftJoin(
                Medication::class,
                'm',
                Join::WITH,
                'm = rm.medication'
            )
            ->leftJoin(
                MedicationFormFactor::class,
                'ff',
                Join::WITH,
                'ff = rm.formFactor'
            )
            ->groupBy('rm.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('rm');

        return $qb->where($qb->expr()->in('rm.id', $ids))
            ->groupBy('rm.id')
            ->getQuery()
            ->getResult();
    }
}