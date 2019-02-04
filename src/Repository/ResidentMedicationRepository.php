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

    /**
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(array $residentIds)
    {
        $qb = $this->createQueryBuilder('rm');

        return $qb
            ->select('
                    r.id as residentId,
                    p.firstName as physicianFirstName,
                    p.lastName as physicianLastName,
                    p.officePhone as physicianOfficePhone,
                    rm.prescriptionNumber as prescriptionNumber,
                    rm.treatment as medicationTreatment,
                    rm.discontinued as medicationDiscont,
                    rm.prn as medicationPrn,
                    rm.hs as medicationHs,
                    rm.pm as medicationPm,
                    rm.nn as medicationNn,
                    rm.am as medicationAm,
                    rm.notes as notes,
                    rm.dosage as dosage,
                    rm.dosageUnit as dosageUnit,
                    m.title as medication
            ')
            ->innerJoin(
                Medication::class,
                'm',
                Join::WITH,
                'rm.medication = m'
            )
            ->innerJoin(
                Physician::class,
                'p',
                Join::WITH,
                'rm.physician = p'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'rm.resident = r'
            )
            ->where($qb->expr()->in('r.id', $residentIds))
            ->orderBy('rm.treatment', 'ASC')
            ->addOrderBy('rm.am', 'DESC')
            ->addOrderBy('rm.nn', 'DESC')
            ->addOrderBy('rm.pm', 'DESC')
            ->addOrderBy('rm.hs', 'DESC')
            ->addOrderBy('rm.prn', 'DESC')
            ->addOrderBy('m.title')
            ->groupBy('rm.id')
            ->getQuery()
            ->getResult();
    }
}