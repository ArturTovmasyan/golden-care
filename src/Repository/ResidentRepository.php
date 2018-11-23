<?php

namespace App\Repository;

use App\Entity\FacilityRoom;
use App\Entity\Physician;
use App\Entity\Resident;
use App\Entity\ResidentApartmentOption;
use App\Entity\ResidentFacilityOption;
use App\Entity\ResidentRegionOption;
use App\Entity\Salutation;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentRepository
 * @package App\Repository
 */
class ResidentRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Resident::class, 'r')
            ->leftJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'sal = r.salutation'
            )
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->leftJoin(
                Physician::class,
                'p',
                Join::WITH,
                'p = r.physician'
            )
            ->groupBy('r.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('r');

        return $qb->where($qb->expr()->in('r.id', $ids))
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param $state
     * @return mixed
     */
    public function getByTypeAndState($type, $state)
    {
        $queryBuilder = $this->createQueryBuilder('r');

        switch ($type) {
            case \App\Model\Resident::TYPE_APARTMENT:
                $queryBuilder->leftJoin(
                    ResidentApartmentOption::class,
                    'o',
                    Join::WITH,
                    'o.resident = r'
                );
                break;
            case \App\Model\Resident::TYPE_REGION:
                $queryBuilder->leftJoin(
                    ResidentRegionOption::class,
                    'o',
                    Join::WITH,
                    'o.resident = r'
                );
                break;
            default:
                $queryBuilder
                    ->leftJoin(
                    ResidentFacilityOption::class,
                    'o',
                    Join::WITH,
                    'o.resident = r'
                    );
        }

        $queryBuilder->where('r.type = :type AND o.state = :state')
            ->setParameter("type", $type)
            ->setParameter('state', $state);

        return $queryBuilder
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }
}
