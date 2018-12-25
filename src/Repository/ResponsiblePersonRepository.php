<?php

namespace App\Repository;

use App\Entity\CityStateZip;
use App\Entity\Resident;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePerson;
use App\Entity\ResponsiblePersonPhone;
use App\Entity\Salutation;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResponsiblePersonRepository
 * @package App\Repository
 */
class ResponsiblePersonRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResponsiblePerson::class, 'rp')
            ->leftJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'sal = rp.salutation'
            )
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'csz = rp.csz'
            )
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = rp.space'
            )
            ->groupBy('rp.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('rp');

        return $qb->where($qb->expr()->in('rp.id', $ids))
            ->groupBy('rp.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(array $residentIds)
    {
        $qb = $this->createQueryBuilder('rp');

        return $qb
            ->select('
                    rp.id as id,
                    r.id as residentId,
                    rp.firstName as firstName,
                    rp.lastName as lastName,
                    rp.address_1 as address,
                    rp.financially as financially,
                    rp.emergency as emergency,
                    csz.stateFull as state,
                    csz.zipMain as zip,
                    csz.city as city,
                    rpp.type as phoneType,
                    rpp.extension as phoneExtension,
                    rpp.number as phoneNumber
            ')
            ->innerJoin(
                ResidentResponsiblePerson::class,
                'rrp',
                Join::WITH,
                'rrp.responsiblePerson = rp'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'rrp.resident = r'
            )
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'rp.csz = csz'
            )
            ->leftJoin(
                ResponsiblePersonPhone::class,
                'rpp',
                Join::WITH,
                'rpp.responsiblePerson = rp'
            )
            ->where($qb->expr()->in('r.id', $residentIds))
            ->groupBy('rpp.id')
            ->getQuery()
            ->getResult();
    }
}
