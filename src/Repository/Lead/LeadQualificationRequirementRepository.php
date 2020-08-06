<?php

namespace App\Repository\Lead;

use App\Entity\Lead\Lead;
use App\Entity\Lead\QualificationRequirement;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class LeadQualificationRequirementRepository
 * @package App\Repository
 */
class LeadQualificationRequirementRepository extends EntityRepository
{
    /**
     * @param $leadId
     * @return mixed
     */
    public function getBy($leadId)
    {
        $qb = $this
            ->createQueryBuilder('lqr')
            ->innerJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = lqr.lead'
            )
            ->where('l.id = :leadId')
            ->setParameter('leadId', $leadId);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function getByLeadIds($ids)
    {
        $qb = $this
            ->createQueryBuilder('lqr')
            ->innerJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = lqr.lead'
            )
            ->where('l.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->orderBy('l.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function getByQualificationRequirement($ids)
    {
        $qb = $this
            ->createQueryBuilder('lqr')
            ->innerJoin(
                QualificationRequirement::class,
                'qr',
                Join::WITH,
                'qr = lqr.qualificationRequirement'
            )
            ->where('qr.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this
            ->createQueryBuilder('lqr')
            ->where('lqr.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb->groupBy('lqr.id')
            ->getQuery()
            ->getResult();
    }
}