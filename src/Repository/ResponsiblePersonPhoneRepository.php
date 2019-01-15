<?php

namespace App\Repository;

use App\Entity\ResponsiblePerson;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class ResponsiblePersonPhoneRepository
 * @package App\Repository
 */
class ResponsiblePersonPhoneRepository extends EntityRepository
{
    /**
     * @param array $responsiblePersonIds
     * @return mixed
     */
    public function getByResponsiblePersonIds(array $responsiblePersonIds)
    {
        $qb = $this->createQueryBuilder('rpp');

        return $qb
            ->select('
                    rpp.id as id,
                    rp.id as rpId,
                    rpp.primary as primary,
                    rpp.type as type,
                    rpp.extension as extension,
                    rpp.number as number
            ')
            ->innerJoin(
                ResponsiblePerson::class,
                'rp',
                Join::WITH,
                'rpp.responsiblePerson = rp'
            )

            ->where($qb->expr()->in('rp.id', $responsiblePersonIds))
            ->groupBy('rpp.id')
            ->getQuery()
            ->getResult();
    }
}
