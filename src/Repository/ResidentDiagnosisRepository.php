<?php

namespace App\Repository;

use App\Entity\Diagnosis;
use App\Entity\ResidentDiagnosis;
use App\Entity\Resident;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentDiagnosisRepository
 * @package App\Repository
 */
class ResidentDiagnosisRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentDiagnosis::class, 'rd')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rd.resident'
            )
            ->leftJoin(
                Diagnosis::class,
                'd',
                Join::WITH,
                'd = rd.diagnosis'
            )
            ->groupBy('rd.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('rd');

        return $qb->where($qb->expr()->in('rd.id', $ids))
            ->groupBy('rd.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(array $residentIds)
    {
        $qb = $this->createQueryBuilder('rd');

        return $qb
            ->select('
                    r.id as residentId,
                    rd.id as diagnosisId,
                    rd.type as diagnosisType,
                    d.title as diagnosisTitle
            ')
            ->innerJoin(
                Diagnosis::class,
                'd',
                Join::WITH,
                'rd.diagnosis = d'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'rd.resident = r'
            )
            ->where($qb->expr()->in('r.id', $residentIds))
            ->orderBy('rd.type', 'ASC')
            ->addOrderBy('d.title')
            ->groupBy('rd.id')
            ->getQuery()
            ->getResult();
    }
}