<?php

namespace App\Repository;

use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ContractAction;
use App\Entity\ContractApartmentOption;
use App\Entity\ContractFacilityOption;
use App\Entity\ContractRegionOption;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\Contract;
use App\Model\ContractState;
use App\Model\ContractType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnexpectedResultException;

/**
 * Class ContractRepository
 * @package App\Repository
 */
class ContractRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Contract::class, 'c')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = c.resident'
            )
            ->groupBy('c.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('c');

        return $qb->where($qb->expr()->in('c.id', $ids))
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }
}