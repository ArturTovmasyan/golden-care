<?php

namespace App\Repository;

use App\Entity\Apartment;
use App\Entity\ApartmentRoom;
use App\Entity\Facility;
use App\Entity\FacilityRoom;
use App\Entity\Physician;
use App\Entity\Region;
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
     * @param $id
     * @param $state
     * @return mixed
     */
    public function getByTypeAndState($type, $id, $state)
    {
        $queryBuilder = $this->createQueryBuilder('r');

        switch ($type) {
            case \App\Model\Resident::TYPE_APARTMENT:
                $queryBuilder
                    ->leftJoin(
                        ResidentApartmentOption::class,
                        'o',
                        Join::WITH,
                        'o.resident = r'
                    )
                    ->leftJoin(
                        ApartmentRoom::class,
                        'ar',
                        Join::WITH,
                        'o.apartmentRoom = ar'
                    )
                    ->leftJoin(
                        Apartment::class,
                        'a',
                        Join::WITH,
                        'ar.apartment = a'
                    )
                    ->where('a.id = :id')
                    ->setParameter('id', $id);
                break;
            case \App\Model\Resident::TYPE_REGION:
                $queryBuilder
                    ->leftJoin(
                        ResidentRegionOption::class,
                        'o',
                        Join::WITH,
                        'o.resident = r'
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
                    ResidentFacilityOption::class,
                    'o',
                    Join::WITH,
                    'o.resident = r'
                    )
                    ->leftJoin(
                        FacilityRoom::class,
                        'fr',
                        Join::WITH,
                        'o.facilityRoom = fr'
                    )
                    ->leftJoin(
                        Facility::class,
                        'f',
                        Join::WITH,
                        'fr.facility = f'
                    )
                    ->where('f.id = :id')
                    ->setParameter('id', $id);
        }

        $queryBuilder->andWhere('r.type = :type AND o.state = :state')
            ->setParameter("type", $type)
            ->setParameter('state', $state);

        return $queryBuilder
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }
}
