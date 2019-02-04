<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\CityStateZip;
use App\Entity\Contract;
use App\Entity\ContractAction;
use App\Entity\ContractApartmentOption;
use App\Entity\ContractFacilityOption;
use App\Entity\ContractRegionOption;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Physician;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentPhysician;
use App\Entity\Salutation;
use App\Entity\Space;
use App\Entity\Speciality;
use App\Model\ContractState;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PhysicianRepository
 * @package App\Repository
 */
class PhysicianRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param int|bool $spaceId
     */
    public function search(QueryBuilder $queryBuilder, $spaceId = false)
    {
        $queryBuilder
            ->from(Physician::class, 'p')
            ->leftJoin(
                Speciality::class,
                'sp',
                Join::WITH,
                'sp = p.speciality'
            )
            ->leftJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'sal = p.salutation'
            )
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = p.space'
            )
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'csz = p.csz'
            )
            ->groupBy('p.id');

        if ($spaceId) {
            $queryBuilder
                ->where('p.space = :spaceId')
                ->setParameter('spaceId', $spaceId);
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Space $space
     */
    public function searchBySpace(QueryBuilder $queryBuilder, Space $space)
    {
        $queryBuilder
            ->from(Physician::class, 'p')
            ->leftJoin(
                Speciality::class,
                'sp',
                Join::WITH,
                'sp = p.speciality'
            )
            ->leftJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'sal = p.salutation'
            )
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'csz = p.csz'
            )
            ->where('p.space = :space')
            ->setParameter('space', $space)
            ->groupBy('p.id');
    }

    /**
     * @param Space $space
     * @param $id
     * @return mixed
     */
    public function findBySpaceAndId(Space $space, $id)
    {
        try {
            return $this->createQueryBuilder('p')
                ->where('p.space = :space AND p.id=:id')
                ->setParameter('space', $space)
                ->setParameter('id', $id)
                ->groupBy('p.id')
                ->getQuery()
                ->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException | \Doctrine\ORM\NonUniqueResultException $e) {
            throw new PhysicianNotFoundException();
        }
    }

    /**
     * @param Space $space
     * @param $id
     * @return mixed
     */
    public function findBySpace(Space $space)
    {
        try {
            return $this->createQueryBuilder('p')
                ->where('p.space = :space')
                ->setParameter('space', $space)
                ->groupBy('p.id')
                ->getQuery()
                ->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException | \Doctrine\ORM\NonUniqueResultException $e) {
            throw new PhysicianNotFoundException();
        }
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('p');

        return $qb->where($qb->expr()->in('p.id', $ids))
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $ids
     * @param Space $space
     * @return mixed
     */
    public function findByIdsAndSpace($ids, Space $space)
    {
        $qb = $this->createQueryBuilder('p');

        return $qb->where($qb->expr()->in('p.id', $ids))
            ->andWhere('p.space = :space')
            ->setParameter('space', $space)
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(array $residentIds)
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->select(
                'p.id as id,
                    r.id as residentId,
                    p.firstName as firstName,
                    p.lastName as lastName,
                    sal.title as salutation,
                    p.address_1 as address,
                    rp.primary as primary,
                    csz.stateFull as state,
                    csz.zipMain as zip,
                    csz.city as city,
                    p.officePhone as officePhone,
                    p.emergencyPhone as emergencyPhone,
                    p.fax as fax'
            )
            ->innerJoin(
                ResidentPhysician::class,
                'rp',
                Join::WITH,
                'rp.physician = p'
            )
            ->innerJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'p.salutation = sal'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'rp.resident = r'
            )
            ->innerJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'p.csz = csz'
            )
            ->where($qb->expr()->in('r.id', $residentIds))
            ->orderBy('rp.primary', 'DESC')
            ->groupBy('rp.id')
            ->getQuery()
            ->getResult();
    }
}
