<?php

namespace App\Repository;

use App\Entity\Diagnosis;
use App\Entity\Resident;
use App\Entity\ResidentDiagnosis;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class Diagnosis
 * @package App\Repository
 */
class DiagnosisRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Diagnosis::class, 'd')
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = d.space'
            )
            ->groupBy('d.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('d');

        return $qb->where($qb->expr()->in('d.id', $ids))
            ->groupBy('d.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(array $residentIds)
    {
        $qb = $this->createQueryBuilder('d');

        return $qb
            ->select('
                    r.id as residentId,
                    rd.id as diagnosisId,
                    rd.type as diagnosisType,
                    d.title as diagnosisTitle
            ')
            ->innerJoin(
                ResidentDiagnosis::class,
                'rd',
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
