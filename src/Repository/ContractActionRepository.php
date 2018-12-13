<?php

namespace App\Repository;

use App\Entity\Contract;
use App\Entity\ContractAction;
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
}