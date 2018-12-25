<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Entity\Contract;
use App\Entity\ContractAction;
use App\Model\ContractState;
use App\Model\ContractType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ContractActionRepository
 * @package App\Repository
 */
class ContractActionRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ContractAction::class, 'ca')
            ->leftJoin(
                Contract::class,
                'c',
                Join::WITH,
                'c = ca.contract'
            )
            ->groupBy('ca.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('ca');

        return $qb->where($qb->expr()->in('ca.id', $ids))
            ->groupBy('ca.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getContractLastAction($id)
    {
        $qb = $this->createQueryBuilder('ca');

        return $qb
            ->join('ca.contract', 'c')
            ->where('c.id=:id')
            ->setParameter('id', $id)
            ->orderBy('ca.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getActiveByResident($id)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->select('ca, c')
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->where('ca.state=:state AND ca.end IS NULL')
            ->andWhere('r.id=:id')
            ->setParameter('id', $id)
            ->setParameter('state', ContractState::ACTIVE);

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $type
     * @param $id
     * @return mixed
     */
    public function getDataByResident($type, $id)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->select('ca, c')
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->where('ca.state=:state AND ca.end IS NULL')
            ->andWhere('r.id=:id')
            ->andWhere('c.type=:type')
            ->setParameter('type', $type)
            ->setParameter('id', $id)
            ->setParameter('state', ContractState::ACTIVE);

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect('o')
                    ->join('c.contractFacilityOption', 'o');
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->addSelect('o')
                    ->join('c.contractApartmentOption', 'o');
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->addSelect('o')
                    ->join('c.contractRegionOption', 'o');
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $type
     * @param $id
     * @return mixed
     */
    public function getResidentByBed($type, $id)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->select('r.id AS residentId')
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->where('ca.state=:state AND ca.end IS NULL')
            ->andWhere('c.type=:type')
            ->setParameter('type', $type)
            ->setParameter('state', ContractState::ACTIVE);

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->join('ca.facilityBed', 'fb')
                    ->andWhere('fb.id=:id')
                    ->setParameter('id', $id);
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->join('ca.apartmentBed', 'ab')
                    ->andWhere('ab.id=:id')
                    ->setParameter('id', $id);
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->join('ca.region', 'r')
                    ->andWhere('r.id=:id')
                    ->setParameter('id', $id);
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $type
     * @param $ids
     * @return mixed
     */
    public function getResidentsByBeds($type, $ids)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->select(
                'ca AS action',
                'c AS contract',
                'r AS resident'
            )
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->where('ca.state=:state AND ca.end IS NULL')
            ->andWhere('c.type=:type')
            ->setParameter('type', $type)
            ->setParameter('state', ContractState::ACTIVE);

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect('fb.id AS bedId')
                    ->join('ca.facilityBed', 'fb')
                    ->andWhere('fb.id IN (:ids)')
                    ->setParameter('ids', $ids);
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->addSelect('ab.id AS bedId')
                    ->join('ca.apartmentBed', 'ab')
                    ->andWhere('ab.id IN (:ids)')
                    ->setParameter('ids', $ids);
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->addSelect('r.id AS regionId')
                    ->join('ca.region', 'r')
                    ->andWhere('r.id IN (:ids)')
                    ->setParameter('ids', $ids);
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param $ids
     * @return mixed
     */
    public function getBeds($type, $ids)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->where('ca.state=:state AND ca.end IS NULL')
            ->andWhere('c.type=:type')
            ->setParameter('type', $type)
            ->setParameter('state', ContractState::ACTIVE);

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->select('fb.id AS bedId')
                    ->join('ca.facilityBed', 'fb')
                    ->andWhere('fb.id IN (:ids)')
                    ->setParameter('ids', $ids);
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->select('ab.id AS bedId')
                    ->join('ca.apartmentBed', 'ab')
                    ->andWhere('ab.id IN (:ids)')
                    ->setParameter('ids', $ids);
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->select('r.id AS regionId')
                    ->join('ca.region', 'r')
                    ->andWhere('r.id IN (:ids)')
                    ->setParameter('ids', $ids);
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param id
     * @return mixed
     */
    public function getActiveResidentsByStrategy($type, $id)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->select(
                'r.id AS id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'rs.title AS salutation'
            )
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->leftJoin('r.salutation', 'rs')
            ->where('ca.state=:state AND ca.end IS NULL')
            ->andWhere('c.type=:type')
            ->setParameter('type', $type)
            ->setParameter('state', ContractState::ACTIVE);

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'fbr.number AS room_number',
                        'fb.number AS bed_number'
                    )
                    ->join('ca.facilityBed', 'fb')
                    ->join('fb.room', 'fbr')
                    ->join('fbr.facility', 'fbrf')
                    ->andWhere('fbrf.id=:id')
                    ->setParameter('id', $id);
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->addSelect(
                        'abr.number AS room_number',
                        'ab.number AS bed_number'
                    )
                    ->join('ca.apartmentBed', 'ab')
                    ->join('ab.room', 'abr')
                    ->join('ab.apartment', 'abra')
                    ->andWhere('abra.id=:id')
                    ->setParameter('id', $id);
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->join('ca.region', 'r')
                    ->andWhere('r.id=:id')
                    ->setParameter('id', $id);
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}