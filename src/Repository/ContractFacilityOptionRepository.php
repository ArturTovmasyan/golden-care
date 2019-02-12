<?php

namespace App\Repository;

use App\Entity\Contract;
use App\Entity\Resident;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class ContractFacilityOptionRepository
 * @package App\Repository
 */
class ContractFacilityOptionRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param Contract $contract
     * @return mixed
     */
    public function getOneBy(Space $space = null, Contract $contract)
    {
        $qb = $this
            ->createQueryBuilder('o')
            ->innerJoin(
                Contract::class,
                'c',
                Join::WITH,
                'c = o.contract'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = c.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('c = :contract')
            ->setParameter('contract', $contract);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }
}
