<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Medication;
use App\Entity\MedicationFormFactor;
use App\Entity\Physician;
use App\Entity\ResidentMedication;
use App\Entity\Resident;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentMedicationRepository
 * @package App\Repository
 */
class ResidentMedicationRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(ResidentMedication::class, 'rm')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rm.resident'
            )
            ->innerJoin(
                Physician::class,
                'p',
                Join::WITH,
                'p = rm.physician'
            )
            ->innerJoin(
                Medication::class,
                'm',
                Join::WITH,
                'm = rm.medication'
            )
            //TODO will be change to innerJoin
            ->leftJoin(
                MedicationFormFactor::class,
                'ff',
                Join::WITH,
                'ff = rm.formFactor'
            );

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('rm.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('rm.id')
            ->orderBy('rm.discontinued','ASC');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @param null $medication_id
     * @param boolean $noDiscontinued
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, $id, $medication_id = null, $noDiscontinued)
    {
        $qb = $this
            ->createQueryBuilder('rm')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rm.resident'
            )
            ->where('r.id = :id')
            ->setParameter('id', $id);

        if ($medication_id !== null) {
            $qb
                ->andWhere('rm.medication = :medication_id')
                ->setParameter('medication_id', $medication_id);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rm.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        if ($noDiscontinued) {
            $qb
                ->andWhere('rm.discontinued = :discontinued')
                ->setParameter('discontinued', 0);
        }

        return $qb
            ->orderBy('rm.discontinued','ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @return mixed
     */
    public function getOne(Space $space = null, array $entityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('rm')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rm.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('rm.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rm.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $ids
     * @return mixed
     */
    public function findByIds(Space $space = null, array $entityGrants = null, $ids)
    {
        $qb = $this
            ->createQueryBuilder('rm')
            ->where('rm.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rm.resident'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rm.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('rm.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(Space $space = null, array $entityGrants = null, array $residentIds)
    {
        $qb = $this->createQueryBuilder('rm');

        $qb
            ->select('
                    r.id as residentId,
                    p.firstName as physicianFirstName,
                    p.lastName as physicianLastName,
                    p.id as pId,
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
            ->where('r.id IN (:residentIds)')
            ->andWhere('rm.discontinued = 0')
            ->setParameter('residentIds', $residentIds);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rm.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
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

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param null $mappedBy
     * @param null $id
     * @param array|null $ids
     * @return mixed
     */
    public function getRelatedData(Space $space = null, array $entityGrants = null, $mappedBy = null, $id = null, array $ids = null)
    {
        $qb = $this
            ->createQueryBuilder('rm')
            ->innerJoin(
                Medication::class,
                'm',
                Join::WITH,
                'rm.medication = m'
            )
            ->select('m.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('rm.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('rm.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('rm.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rm.resident'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rm.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}