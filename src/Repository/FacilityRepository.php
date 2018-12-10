<?php

namespace App\Repository;

use App\Entity\CityStateZip;
use App\Entity\Facility;
use App\Entity\FacilityRoom;
use App\Entity\Resident;
use App\Entity\ResidentFacilityOption;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityRepository
 * @package App\Repository
 */
class FacilityRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Facility::class, 'f')
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'csz = f.csz'
            )
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = f.space'
            )
            ->groupBy('f.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('f');

        return $qb->where($qb->expr()->in('f.id', $ids))
            ->groupBy('f.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $residentId
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByResident($residentId)
    {
        return $this->createQueryBuilder('f')
            ->innerJoin(
                FacilityRoom::class,
                'fr',
                Join::WITH,
                'f = fr.facility'
            )
            ->innerJoin(
                ResidentFacilityOption::class,
                'rfo',
                Join::WITH,
                'fr = rfo.facilityRoom'
            )
            ->where('rfo.resident = :residentId')
            ->setParameter('residentId', $residentId)
            ->groupBy('f.id')
            ->getQuery()
            ->getSingleResult();
    }
}