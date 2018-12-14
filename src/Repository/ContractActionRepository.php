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
     * @param $type
     * @param $ids
     * @return mixed
     */
    public function getResidents($type, $ids)
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
}