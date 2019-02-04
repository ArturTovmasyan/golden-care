<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\CareLevel;
use App\Entity\CityStateZip;
use App\Entity\Contract;
use App\Entity\ContractAction;
use App\Entity\ContractApartmentOption;
use App\Entity\ContractFacilityOption;
use App\Entity\ContractRegionOption;
use App\Entity\Diet;
use App\Entity\DiningRoom;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Physician;
use App\Entity\Region;
use App\Entity\Relationship;
use App\Entity\Resident;
use App\Entity\ResidentDiet;
use App\Entity\ResidentPhysician;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePerson;
use App\Entity\ResponsiblePersonPhone;
use App\Entity\Salutation;
use App\Entity\Space;
use App\Model\ContractState;
use App\Model\ContractType;
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
     * @return mixed
     */
    public function getNoContractResidents()
    {
        $qb = $this->createQueryBuilder('r');

        $qb
            ->select(
                'r.id AS id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'rs.title AS salutation'
            )
            ->leftJoin('r.salutation', 'rs')
            ->where('r.id NOT IN (SELECT cr.id FROM App:Contract c JOIN c.resident cr)');

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param null $typeId
     * @param null $residentId
     * @return mixed
     */
    public function getResidentsInfoByTypeOrId($type, $typeId = null, $residentId = null)
    {
        /**
         * @var ContractAction $contractAction
         */
        $qb = $this->createQueryBuilder('r');

        if ($residentId) {
            $contractAction = $this->_em->getRepository(ContractAction::class)->getActiveByResident($residentId);

            if ($contractAction === null) {
                throw new ResidentNotFoundException();
            }

            $type = 0;

            if ($contractAction->getContract()) {
                $type = $contractAction->getContract()->getType();
            }
        }

        $qb
            ->select(
                'r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    ca.dnr as dnr,
                    ca.ambulatory as ambulatory,
                    r.birthday as birthday,
                    sal.title as salutation'
            )
            ->innerJoin(
                Contract::class,
                'c',
                Join::WITH,
                'c.resident = r'
            )
            ->innerJoin(
                ContractAction::class,
                'ca',
                Join::WITH,
                'ca.contract = c'
            )
            ->innerJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'r.salutation = sal'
            )
            ->where('ca.state=:state AND ca.end IS NULL')
            ->setParameter('state', ContractState::ACTIVE);

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.shorthand as typeShorthand,
                        f.address as address,
                        f.license as license,
                        fr.number as roomNumber,
                        fr.floor as floor,
                        fb.number as bedNumber'
                    )
                    ->innerJoin(
                        ContractFacilityOption::class,
                        'cfo',
                        Join::WITH,
                        'cfo.contract = c'
                    )
                    ->innerJoin(
                        DiningRoom::class,
                        'dr',
                        Join::WITH,
                        'cfo.diningRoom = dr'
                    )
                    ->innerJoin(
                        FacilityBed::class,
                        'fb',
                        Join::WITH,
                        'cfo.facilityBed = fb'
                    )
                    ->innerJoin(
                        FacilityRoom::class,
                        'fr',
                        Join::WITH,
                        'fb.room = fr'
                    )
                    ->innerJoin(
                        Facility::class,
                        'f',
                        Join::WITH,
                        'fr.facility = f'
                    );

                $qb
                    ->orderBy('f.name')
                    ->addOrderBy('fr.number')
                    ->addOrderBy('fb.number');

                if ($typeId) {
                    $qb
                        ->andWhere('f.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->addSelect(
                        'a.id as typeId,
                        a.name as typeName,
                        a.shorthand as typeShorthand,
                        a.address as address,
                        a.license as license,
                        ar.number as roomNumber,
                        ar.floor as floor,
                        ab.number as bedNumber'
                    )
                    ->innerJoin(
                        ContractApartmentOption::class,
                        'cao',
                        Join::WITH,
                        'cao.contract = c'
                    )
                    ->innerJoin(
                        ApartmentBed::class,
                        'ab',
                        Join::WITH,
                        'cao.apartmentBed = ab'
                    )
                    ->innerJoin(
                        ApartmentRoom::class,
                        'ar',
                        Join::WITH,
                        'ab.room = ar'
                    )
                    ->innerJoin(
                        Apartment::class,
                        'a',
                        Join::WITH,
                        'ar.apartment = a'
                    );

                $qb
                    ->orderBy('a.name')
                    ->addOrderBy('ar.number')
                    ->addOrderBy('ab.number');

                if ($typeId) {
                    $qb
                        ->andWhere('a.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->addSelect(
                        'cro.address as address,
                        csz.city as city,
                        csz.stateAbbr as state,
                        csz.zipMain as zip,
                        reg.id as typeId,
                        reg.shorthand as typeShorthand,
                        reg.name as typeName'
                    )
                    ->innerJoin(
                        ContractRegionOption::class,
                        'cro',
                        Join::WITH,
                        'cro.contract = c'
                    )
                    ->innerJoin(
                        Region::class,
                        'reg',
                        Join::WITH,
                        'cro.region = reg'
                    )
                    ->innerJoin(
                        CityStateZip::class,
                        'csz',
                        Join::WITH,
                        'cro.csz = csz'
                    );

                $qb
                    ->orderBy('reg.name');

                if ($typeId) {
                    $qb
                        ->andWhere('reg.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        if ($residentId) {
            return $qb
                ->andWhere('r.id = :id')
                ->setParameter('id', $residentId)
                ->getQuery()
                ->getResult();
        }

        return $qb
            ->andWhere('c.type = :type')
            ->setParameter('type', $type)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param null $typeId
     * @param null $residentId
     * @return mixed
     */
    public function getResidentsInfoWithCareGroupByTypeOrId($type, $typeId = null, $residentId = null)
    {
        /**
         * @var ContractAction $contractAction
         */
        $qb = $this->createQueryBuilder('r');

        if ($residentId) {
            $contractAction = $this->_em->getRepository(ContractAction::class)->getActiveByResident($residentId);

            if ($contractAction === null) {
                throw new ResidentNotFoundException();
            }

            $type = 0;

            if ($contractAction->getContract()) {
                $type = $contractAction->getContract()->getType();
            }
        }

        $qb
            ->select(
                'r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    ca.dnr as dnr,
                    r.birthday as birthday,
                    sal.title as salutation'
            )
            ->innerJoin(
                Contract::class,
                'c',
                Join::WITH,
                'c.resident = r'
            )
            ->innerJoin(
                ContractAction::class,
                'ca',
                Join::WITH,
                'ca.contract = c'
            )
            ->innerJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'r.salutation = sal'
            )
            ->where('ca.state=:state AND ca.end IS NULL')
            ->setParameter('state', ContractState::ACTIVE);

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.address as address,
                        f.license as license,
                        fr.number as roomNumber,
                        fr.floor as floor,
                        fb.number as bedNumber,
                        cfo.careGroup as careGroup'
                    )
                    ->innerJoin(
                        ContractFacilityOption::class,
                        'cfo',
                        Join::WITH,
                        'cfo.contract = c'
                    )
                    ->innerJoin(
                        DiningRoom::class,
                        'dr',
                        Join::WITH,
                        'cfo.diningRoom = dr'
                    )
                    ->innerJoin(
                        FacilityBed::class,
                        'fb',
                        Join::WITH,
                        'cfo.facilityBed = fb'
                    )
                    ->innerJoin(
                        FacilityRoom::class,
                        'fr',
                        Join::WITH,
                        'fb.room = fr'
                    )
                    ->innerJoin(
                        Facility::class,
                        'f',
                        Join::WITH,
                        'fr.facility = f'
                    );

                $qb
                    ->orderBy('f.name')
                    ->addOrderBy('cfo.careGroup')
                    ->addOrderBy('fr.number')
                    ->addOrderBy('fb.number');

                if ($typeId) {
                    $qb
                        ->andWhere('f.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->addSelect(
                        'cro.address as address,
                        csz.city as city,
                        csz.stateAbbr as state,
                        csz.zipMain as zip,
                        reg.id as typeId,
                        reg.name as typeName,
                        cro.careGroup as careGroup'
                    )
                    ->innerJoin(
                        ContractRegionOption::class,
                        'cro',
                        Join::WITH,
                        'cro.contract = c'
                    )
                    ->innerJoin(
                        Region::class,
                        'reg',
                        Join::WITH,
                        'cro.region = reg'
                    )
                    ->innerJoin(
                        CityStateZip::class,
                        'csz',
                        Join::WITH,
                        'cro.csz = csz'
                    );

                $qb
                    ->orderBy('reg.name')
                    ->addOrderBy('cro.careGroup');

                if ($typeId) {
                    $qb
                        ->andWhere('reg.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        if ($residentId) {
            return $qb
                ->andWhere('r.id = :id')
                ->setParameter('id', $residentId)
                ->getQuery()
                ->getResult();
        }

        return $qb
            ->andWhere('c.type = :type')
            ->setParameter('type', $type)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param null $typeId
     * @param null $residentId
     * @return mixed
     */
    public function getDietaryRestrictionsInfo($type, $typeId = null, $residentId = null)
    {
        /**
         * @var ContractAction $contractAction
         */
        $qb = $this->createQueryBuilder('r');

        if ($residentId) {
            $contractAction = $this->_em->getRepository(ContractAction::class)->getActiveByResident($residentId);

            if ($contractAction === null) {
                throw new ResidentNotFoundException();
            }

            $type = 0;

            if ($contractAction->getContract()) {
                $type = $contractAction->getContract()->getType();
            }
        }

        $qb
            ->select(
                'r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    c.start as startDate,
                    ca.state as state,
                    ca.dnr as dnr,
                    ca.polst as polst,
                    ca.ambulatory as ambulatory,
                    ca.careGroup as careGroup,
                    cl.title as careLevel,
                    r.birthday as birthday,
                    r.gender as gender,
                    sal.title as salutation'
            )
            ->innerJoin(
                Contract::class,
                'c',
                Join::WITH,
                'c.resident = r'
            )
            ->innerJoin(
                ContractAction::class,
                'ca',
                Join::WITH,
                'ca.contract = c'
            )
            ->innerJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'r.salutation = sal'
            )
            ->innerJoin(
                CareLevel::class,
                'cl',
                Join::WITH,
                'ca.careLevel = cl'
            )
            ->where('ca.state=:state AND ca.end IS NULL')
            ->setParameter('state', ContractState::ACTIVE);

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.address as address,
                        f.license as license,
                        f.phone as typePhone,
                        f.fax as typeFax,
                        fr.number as roomNumber,
                        fr.floor as floor,
                        fb.number as bedNumber,
                        dr.title as diningRoom'
                    )
                    ->innerJoin(
                        ContractFacilityOption::class,
                        'cfo',
                        Join::WITH,
                        'cfo.contract = c'
                    )
                    ->innerJoin(
                        DiningRoom::class,
                        'dr',
                        Join::WITH,
                        'cfo.diningRoom = dr'
                    )
                    ->innerJoin(
                        FacilityBed::class,
                        'fb',
                        Join::WITH,
                        'cfo.facilityBed = fb'
                    )
                    ->innerJoin(
                        FacilityRoom::class,
                        'fr',
                        Join::WITH,
                        'fb.room = fr'
                    )
                    ->innerJoin(
                        Facility::class,
                        'f',
                        Join::WITH,
                        'fr.facility = f'
                    );

                $qb
                    ->orderBy('f.name')
                    ->addOrderBy('dr.title')
                    ->addOrderBy('fr.number')
                    ->addOrderBy('fb.number');

                if ($typeId) {
                    $qb
                        ->andWhere('f.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->addSelect(
                        'cro.address as address,
                        csz.city as city,
                        csz.stateAbbr as state,
                        csz.zipMain as zip,
                        reg.id as typeId,
                        reg.name as typeName,
                        reg.phone as typePhone,
                        reg.fax as typeFax'
                    )
                    ->innerJoin(
                        ContractRegionOption::class,
                        'cro',
                        Join::WITH,
                        'cro.contract = c'
                    )
                    ->innerJoin(
                        Region::class,
                        'reg',
                        Join::WITH,
                        'cro.region = reg'
                    )
                    ->innerJoin(
                        CityStateZip::class,
                        'csz',
                        Join::WITH,
                        'cro.csz = csz'
                    );

                $qb
                    ->orderBy('reg.name');

                if ($typeId) {
                    $qb
                        ->andWhere('reg.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        if ($residentId) {
            return $qb
                ->andWhere('r.id = :id')
                ->setParameter('id', $residentId)
                ->getQuery()
                ->getResult();
        }

        return $qb
            ->andWhere('c.type = :type')
            ->setParameter('type', $type)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param null $typeId
     * @param null $residentId
     * @return mixed
     */
    public function getResidentsFullInfoByTypeOrId($type, $typeId = null, $residentId = null)
    {
        /**
         * @var ContractAction $contractAction
         */
        $qb = $this->createQueryBuilder('r');

        if ($residentId) {
            $contractAction = $this->_em->getRepository(ContractAction::class)->getActiveByResident($residentId);

            if ($contractAction === null) {
                throw new ResidentNotFoundException();
            }

            $type = 0;

            if ($contractAction->getContract()) {
                $type = $contractAction->getContract()->getType();
            }
        }

        $qb
            ->select(
                'r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    c.start as startDate,
                    ca.state as state,
                    ca.dnr as dnr,
                    ca.polst as polst,
                    ca.ambulatory as ambulatory,
                    ca.careGroup as careGroup,
                    cl.title as careLevel,
                    r.birthday as birthday,
                    r.gender as gender,
                    sal.title as salutation'
            )
            ->innerJoin(
                Contract::class,
                'c',
                Join::WITH,
                'c.resident = r'
            )
            ->innerJoin(
                ContractAction::class,
                'ca',
                Join::WITH,
                'ca.contract = c'
            )
            ->innerJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'r.salutation = sal'
            )
            ->innerJoin(
                CareLevel::class,
                'cl',
                Join::WITH,
                'ca.careLevel = cl'
            )
            ->where('ca.state=:state AND ca.end IS NULL')
            ->setParameter('state', ContractState::ACTIVE);

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.address as address,
                        f.license as license,
                        f.phone as typePhone,
                        f.fax as typeFax,
                        fr.number as roomNumber,
                        fr.floor as floor,
                        fb.number as bedNumber,
                        dr.title as diningRoom'
                    )
                    ->innerJoin(
                        ContractFacilityOption::class,
                        'cfo',
                        Join::WITH,
                        'cfo.contract = c'
                    )
                    ->innerJoin(
                        DiningRoom::class,
                        'dr',
                        Join::WITH,
                        'cfo.diningRoom = dr'
                    )
                    ->innerJoin(
                        FacilityBed::class,
                        'fb',
                        Join::WITH,
                        'cfo.facilityBed = fb'
                    )
                    ->innerJoin(
                        FacilityRoom::class,
                        'fr',
                        Join::WITH,
                        'fb.room = fr'
                    )
                    ->innerJoin(
                        Facility::class,
                        'f',
                        Join::WITH,
                        'fr.facility = f'
                    );

                $qb
                    ->orderBy('f.name')
                    ->addOrderBy('fr.number')
                    ->addOrderBy('fb.number');

                if ($typeId) {
                    $qb
                        ->andWhere('f.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->addSelect(
                        'cro.address as address,
                        csz.city as city,
                        csz.stateAbbr as state,
                        csz.zipMain as zip,
                        reg.id as typeId,
                        reg.name as typeName,
                        reg.phone as typePhone,
                        reg.fax as typeFax'
                    )
                    ->innerJoin(
                        ContractRegionOption::class,
                        'cro',
                        Join::WITH,
                        'cro.contract = c'
                    )
                    ->innerJoin(
                        Region::class,
                        'reg',
                        Join::WITH,
                        'cro.region = reg'
                    )
                    ->innerJoin(
                        CityStateZip::class,
                        'csz',
                        Join::WITH,
                        'cro.csz = csz'
                    );

                $qb
                    ->orderBy('reg.name');

                if ($typeId) {
                    $qb
                        ->andWhere('reg.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        if ($residentId) {
            return $qb
                ->andWhere('r.id = :id')
                ->setParameter('id', $residentId)
                ->getQuery()
                ->getResult();
        }

        return $qb
            ->andWhere('c.type = :type')
            ->setParameter('type', $type)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }
}
