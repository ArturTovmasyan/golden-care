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

    /**
     * @param $type
     * @param $id
     * @param $state
     * @return mixed
     */
    public function getByTypeAndState($type, $id, $state)
    {
        $queryBuilder = $this->createQueryBuilder('c');

        switch ($type) {
            case ContractType::TYPE_APARTMENT:
                $queryBuilder
                    ->leftJoin(
                        ContractApartmentOption::class,
                        'o',
                        Join::WITH,
                        'o.contract = c'
                    )
                    ->leftJoin(
                        ApartmentBed::class,
                        'ab',
                        Join::WITH,
                        'o.apartmentBed = ab'
                    )
                    ->leftJoin(
                        Apartment::class,
                        'a',
                        Join::WITH,
                        'ab.apartment = a'
                    )
                    ->where('a.id = :id')
                    ->setParameter('id', $id);
                break;
            case ContractType::TYPE_REGION:
                $queryBuilder
                    ->leftJoin(
                        ContractRegionOption::class,
                        'o',
                        Join::WITH,
                        'o.contract = c'
                    )
                    ->leftJoin(
                        Region::class,
                        'r',
                        Join::WITH,
                        'o.region = r'
                    )
                    ->where('r.id = :id')
                    ->setParameter('id', $id);
                break;
            default:
                $queryBuilder
                    ->leftJoin(
                        ContractFacilityOption::class,
                        'o',
                        Join::WITH,
                        'o.contract = c'
                    )
                    ->leftJoin(
                        FacilityBed::class,
                        'fb',
                        Join::WITH,
                        'o.facilityBed = fb'
                    )
                    ->leftJoin(
                        Facility::class,
                        'f',
                        Join::WITH,
                        'fb.facility = f'
                    )
                    ->where('f.id = :id')
                    ->setParameter('id', $id);
        }

        $queryBuilder->andWhere('c.type = :type AND o.state = :state')
            ->setParameter('type', $type)
            ->setParameter('state', $state);

        return $queryBuilder
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }
}