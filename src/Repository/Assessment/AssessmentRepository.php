<?php

namespace App\Repository\Assessment;

use App\Entity\Assessment\Assessment;
use App\Entity\Assessment\Form;
use App\Entity\ResidentAssessment;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AssessmentRepository
 * @package App\Repository\Assessment
 */
class AssessmentRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Assessment::class, 'a')
            ->innerJoin(
                ResidentAssessment::class,
                'ra',
                Join::WITH,
                'a = ra.assessment'
            )
            ->leftJoin(
                Form::class,
                'f',
                Join::WITH,
                'f = a.form'
            )
            ->groupBy('a.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('a');

        return $qb->where($qb->expr()->in('a.id', $ids))
            ->groupBy('a.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $residentId
     * @return mixed
     */
    public function findByResident($residentId)
    {
        return $this
            ->createQueryBuilder('a')
            ->innerJoin(
                ResidentAssessment::class,
                'ra',
                Join::WITH,
                'a = ra.assessment'
            )
            ->where('ra.resident = :residentId')
            ->setParameter('residentId', $residentId)
            ->groupBy('a.id')
            ->getQuery()
            ->getResult();
    }
}