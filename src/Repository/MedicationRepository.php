<?php

namespace App\Repository;

use App\Entity\Medication;
use App\Entity\Physician;
use App\Entity\ResidentMedication;
use App\Entity\Resident;
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

    /**
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(array $residentIds)
    {
        $qb = $this->createQueryBuilder('m');

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
                ResidentMedication::class,
                'rm',
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
