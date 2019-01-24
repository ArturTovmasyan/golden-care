<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Entity\Contract;
use App\Entity\ContractAction;
use App\Model\ContractState;
use App\Model\ContractType;
use App\Util\Common\ImtDateTimeInterval;
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
                    ->join('abr.apartment', 'abra')
                    ->andWhere('abra.id=:id')
                    ->setParameter('id', $id);
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->join('ca.region', 'reg')
                    ->andWhere('reg.id=:id')
                    ->setParameter('id', $id);
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
    public function getInactiveResidentsByStrategy($type, $id)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->select(
                'MAX(ca.id) AS HIDDEN caId',
                'r.id AS id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'rs.title AS salutation'
            )
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->leftJoin('r.salutation', 'rs')
            ->where('ca.end IS NOT NULL')
            ->andWhere('c.type=:type')
            ->setParameter('type', $type);

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'fbr.number AS room_number',
                        'fb.number AS bed_number'
                    )
                    ->join('c.contractFacilityOption', 'o')
                    ->join('o.facilityBed', 'fb')
                    ->join('fb.room', 'fbr')
                    ->join('fbr.facility', 'fbrf')
                    ->andWhere('fbrf.id=:id')
                    ->andWhere('o.state=:state')
                    ->setParameter('id', $id)
                    ->setParameter('state', ContractState::TERMINATED);
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->addSelect(
                        'abr.number AS room_number',
                        'ab.number AS bed_number'
                    )
                    ->join('c.contractApartmentOption', 'o')
                    ->join('o.apartmentBed', 'ab')
                    ->join('ab.room', 'abr')
                    ->join('abr.apartment', 'abra')
                    ->andWhere('abra.id=:id')
                    ->andWhere('o.state=:state')
                    ->setParameter('id', $id)
                    ->setParameter('state', ContractState::TERMINATED);
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->join('c.contractRegionOption', 'o')
                    ->join('o.region', 'reg')
                    ->andWhere('reg.id=:id')
                    ->andWhere('o.state=:state')
                    ->setParameter('id', $id)
                    ->setParameter('state', ContractState::TERMINATED);
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        $qb
            ->andWhere('ca.id IN (SELECT MAX(lca.id) FROM App:ContractAction lca JOIN lca.contract lc JOIN lc.resident lr WHERE lca.state='. ContractState::TERMINATED .' AND lca.end IS NOT NULL GROUP BY lr.id)')
            ->andWhere('r.id NOT IN (SELECT ar.id FROM App:ContractAction aca JOIN aca.contract ac JOIN ac.resident ar WHERE aca.state='. ContractState::ACTIVE .' AND aca.end IS NULL)')
            ->groupBy('r.id');

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param $ids
     * @return mixed
     */
    public function getBedIdAndTypeId($type, $ids)
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
                    ->select('
                        fb.id AS bedId,
                        type.id AS typeId
                    ')
                    ->join('ca.facilityBed', 'fb')
                    ->join('fb.room', 'room')
                    ->join('room.facility', 'type')
                    ->andWhere('fb.id IN (:ids)')
                    ->setParameter('ids', $ids);
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->select('
                        ab.id AS bedId,
                        type.id AS typeId
                    ')
                    ->join('ca.apartmentBed', 'ab')
                    ->join('ab.room', 'room')
                    ->join('room.apartment', 'type')
                    ->andWhere('ab.id IN (:ids)')
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
     * @param ImtDateTimeInterval|null $dateTimeInterval
     * @return QueryBuilder
     */
    public function getContractActionIntervalQb(ImtDateTimeInterval $dateTimeInterval = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ca');
        if ($dateTimeInterval) {
            $qb
                ->join('ca.contract', 'cac')
                ->join('cac.resident', 'car')
                ->where('ca.end IS NULL OR ca.end > = :start')
                ->setParameter('start', $dateTimeInterval->getStart());
            if ($dateTimeInterval->getEnd()) {
                $qb
                    ->andWhere('ca.start < = :end')
                    ->setParameter('end', $dateTimeInterval->getEnd());
            }
        }
        return $qb;
    }

    /**
     * @param ImtDateTimeInterval $dateTimeInterval
     * @return QueryBuilder
     */
    public function getRoomListContractActionIntervalQb(ImtDateTimeInterval $dateTimeInterval): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ca');
        if ($dateTimeInterval) {
            $qb
                ->join('ca.contract', 'cac')
                ->join('cac.resident', 'car')
                ->andWhere('(ca.start < = :end AND ca.start > = :start) OR (ca.start < :start AND (ca.end IS NULL OR ca.end > :start))')
                ->setParameter('start', $dateTimeInterval->getStart())
                ->setParameter('end', $dateTimeInterval->getEnd());
        }
        return $qb;
    }
}