<?php

namespace App\Repository;

use App\Entity\EventDefinition;
use App\Entity\Physician;
use App\Entity\Resident;
use App\Entity\ResidentEvent;
use App\Entity\ResponsiblePerson;
use App\Entity\Salutation;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentEventRepository
 * @package App\Repository
 */
class ResidentEventRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentEvent::class, 're')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = re.resident'
            )
            ->leftJoin(
                EventDefinition::class,
                'ed',
                Join::WITH,
                'ed = re.definition'
            )
            ->leftJoin(
                Physician::class,
                'p',
                Join::WITH,
                'p = re.physician'
            )
            ->leftJoin(
                Salutation::class,
                'ps',
                Join::WITH,
                'ps = p.salutation'
            )
            ->leftJoin(
                ResponsiblePerson::class,
                'rp',
                Join::WITH,
                'rp = re.responsiblePerson'
            )
            ->leftJoin(
                Salutation::class,
                'rps',
                Join::WITH,
                'rps = rp.salutation'
            )
            ->groupBy('re.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('re');

        return $qb->where($qb->expr()->in('re.id', $ids))
            ->groupBy('re.id')
            ->getQuery()
            ->getResult();
    }
}