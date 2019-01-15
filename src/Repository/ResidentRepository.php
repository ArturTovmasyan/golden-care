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
     * @param $type
     * @param $typeId
     * @return mixed
     */
    public function getByType($type, $typeId = false)
    {
        $queryBuilder = $this->createQueryBuilder('r');

        switch ($type) {
            case \App\Model\Resident::TYPE_APARTMENT:
                $queryBuilder
                    ->select('
                        r.id as id, 
                        r.firstName as firstName, 
                        r.lastName as lastName,
                        r.birthday as birthday,
                        c.type as type,
                        a.id as typeId,
                        a.name as name
                    ')
                    ->leftJoin(
                        Contract::class,
                        'c',
                        Join::WITH,
                        'c.resident = r'
                    )
                    ->leftJoin(
                        ContractAction::class,
                        'ca',
                        Join::WITH,
                        'ca.contract = c'
                    )
                    ->leftJoin(
                        ApartmentBed::class,
                        'ab',
                        Join::WITH,
                        'ca.apartmentBed = ab'
                    )
                    ->leftJoin(
                        ApartmentRoom::class,
                        'ar',
                        Join::WITH,
                        'ab.room = ar'
                    )
                    ->leftJoin(
                        Apartment::class,
                        'a',
                        Join::WITH,
                        'ar.apartment = a'
                    );

                if ($typeId) {
                    $queryBuilder
                        ->where('a.id = :id')
                        ->setParameter('id', $typeId);
                }
                break;
            case \App\Model\Resident::TYPE_REGION:
                $queryBuilder
                    ->select('
                        r.id as id, 
                        r.firstName as firstName, 
                        r.lastName as lastName,
                        r.birthday as birthday,
                        c.type as type,
                        reg.id as typeId,
                        reg.name as name
                    ')
                    ->leftJoin(
                        Contract::class,
                        'c',
                        Join::WITH,
                        'c.resident = r'
                    )
                    ->leftJoin(
                        ContractAction::class,
                        'ca',
                        Join::WITH,
                        'ca.contract = c'
                    )
                    ->leftJoin(
                        Region::class,
                        'reg',
                        Join::WITH,
                        'ca.region = reg'
                    );

                if ($typeId) {
                    $queryBuilder
                        ->where('reg.id = :id')
                        ->setParameter('id', $typeId);
                }
                break;
            default:
                $queryBuilder
                    ->select('
                        r.id as id, 
                        r.firstName as firstName, 
                        r.lastName as lastName,
                        r.birthday as birthday,
                        c.type as type,
                        f.id as typeId,
                        f.name as name
                    ')
                    ->leftJoin(
                        Contract::class,
                        'c',
                        Join::WITH,
                        'c.resident = r'
                    )
                    ->leftJoin(
                        ContractAction::class,
                        'ca',
                        Join::WITH,
                        'ca.contract = c'
                    )
                    ->leftJoin(
                        FacilityBed::class,
                        'fb',
                        Join::WITH,
                        'ca.facilityBed = fb'
                    )
                    ->leftJoin(
                        FacilityRoom::class,
                        'fr',
                        Join::WITH,
                        'fb.room = fr'
                    )
                    ->leftJoin(
                        Facility::class,
                        'f',
                        Join::WITH,
                        'fr.facility = f'
                    );
                if ($typeId) {
                    $queryBuilder
                        ->where('f.id = :id')
                        ->setParameter('id', $typeId);
                }
        }

        return $queryBuilder
            ->andWhere('c.type = :type AND ca.state=:state AND ca.end IS NULL')
            ->setParameter("type", $type)
            ->setParameter("state", ContractState::ACTIVE)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param $typeId
     * @return mixed
     */
    public function getContractInfoByType($type, $typeId = false)
    {
        $queryBuilder = $this->createQueryBuilder('r');

        if ($type == \App\Model\Resident::TYPE_APARTMENT) {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    a.id as typeId,
                    ar.number as roomNumber,
                    ab.number as bedNumber,
                    a.name as name
                ')
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
                    ApartmentBed::class,
                    'ab',
                    Join::WITH,
                    'ca.apartmentBed = ab'
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

            if ($typeId) {
                $queryBuilder
                    ->where('a.id = :id')
                    ->setParameter('id', $typeId);
            }
        } else {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    f.id as typeId,
                    fr.number as roomNumber,
                    fb.number as bedNumber,
                    f.name as name
                ')
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
                    FacilityBed::class,
                    'fb',
                    Join::WITH,
                    'ca.facilityBed = fb'
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
            if ($typeId) {
                $queryBuilder
                    ->where('f.id = :id')
                    ->setParameter('id', $typeId);
            }
        }

        $queryBuilder
            ->andWhere('c.type = :type AND ca.state=:state AND ca.end IS NULL')
            ->setParameter("type", $type)
            ->setParameter("state", ContractState::ACTIVE);

        return $queryBuilder
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param $typeId
     * @return mixed
     */
    public function getBowelMovementInfoByType($type, $typeId = false)
    {
        $queryBuilder = $this->createQueryBuilder('r');

        if ($type == \App\Model\Resident::TYPE_REGION) {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    reg.id as typeId,
                    reg.name as name,
                    cro.careGroup'
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
                    ContractRegionOption::class,
                    'cro',
                    Join::WITH,
                    'cro.contract = c'
                )
                ->innerJoin(
                    Region::class,
                    'reg',
                    Join::WITH,
                    'ca.region = reg'
                )
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE)
                ->addOrderBy('cro.careGroup', 'ASC');
            if ($typeId) {
                $queryBuilder
                    ->andWhere('reg.id = :id')
                    ->setParameter('id', $typeId);
            }
        } else {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    f.id as typeId,
                    fr.number as roomNumber,
                    fb.number as bedNumber,
                    f.name as name,
                    cfo.careGroup
                ')
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
                    ContractFacilityOption::class,
                    'cfo',
                    Join::WITH,
                    'cfo.contract = c'
                )
                ->innerJoin(
                    FacilityBed::class,
                    'fb',
                    Join::WITH,
                    'ca.facilityBed = fb'
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
                )
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE)
                ->addOrderBy('fr.number', 'ASC')
                ->addOrderBy('fb.number', 'ASC')
                ->addOrderBy('cfo.careGroup', 'ASC');
            if ($typeId) {
                $queryBuilder
                    ->andWhere('f.id = :id')
                    ->setParameter('id', $typeId);
            }
        }

        return $queryBuilder
            ->andWhere('c.type = :type')
            ->setParameter("type", $type)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param $typeId
     * @return mixed
     */
    public function getManicureInfoByType($type, $typeId = false)
    {
        $queryBuilder = $this->createQueryBuilder('r');

        if ($type == \App\Model\Resident::TYPE_REGION) {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    reg.id as typeId,
                    reg.name as name'
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
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE)
                ->addOrderBy('cro.careGroup', 'ASC');
            if ($typeId) {
                $queryBuilder
                    ->andWhere('reg.id = :id')
                    ->setParameter('id', $typeId);
            }
        } else {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    f.id as typeId,
                    fr.number as roomNumber,
                    fb.number as bedNumber,
                    f.name as name
                ')
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
                    ContractFacilityOption::class,
                    'cfo',
                    Join::WITH,
                    'cfo.contract = c'
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
                )
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE)
                ->addOrderBy('fr.number', 'ASC')
                ->addOrderBy('fb.number', 'ASC')
                ->addOrderBy('cfo.careGroup', 'ASC');
            if ($typeId) {
                $queryBuilder
                    ->andWhere('f.id = :id')
                    ->setParameter('id', $typeId);

            }
        }

        return $queryBuilder
            ->andWhere('c.type = :type')
            ->setParameter("type", $type)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param bool $typeId
     * @return array
     */
    public function getChangeoverNotesInfo($type, $typeId = false)
    {
        $queryBuilder = $this->createQueryBuilder('r');

        if ($type == \App\Model\Resident::TYPE_REGION) {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    reg.id as typeId,
                    reg.name as name,
                    cro.careGroup'
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
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE)
                ->addOrderBy('cro.careGroup', 'ASC');

            if ($typeId) {
                $queryBuilder
                    ->andWhere('reg.id = :id')
                    ->setParameter('id', $typeId);
            }
        } else {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    f.id as typeId,
                    fr.number as roomNumber,
                    fb.number as bedNumber,
                    f.name as name,
                    cfo.careGroup
                ')
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
                    ContractFacilityOption::class,
                    'cfo',
                    Join::WITH,
                    'cfo.contract = c'
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
                )
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE)
                ->addOrderBy('fr.number', 'ASC')
                ->addOrderBy('fb.number', 'ASC')
                ->addOrderBy('cfo.careGroup', 'ASC');

            if ($typeId) {
                $queryBuilder
                    ->andWhere('f.id = :id')
                    ->setParameter('id', $typeId);
            }
        }

        return $queryBuilder
            ->andWhere('c.type = :type')
            ->setParameter("type", $type)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param bool $typeId
     * @return array
     */
    public function getMealMonitorInfo($type, $typeId = false)
    {
        $queryBuilder = $this->createQueryBuilder('r');

        if ($type == \App\Model\Resident::TYPE_REGION) {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    reg.id as typeId,
                    reg.name as name,
                    cro.careGroup'
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
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE)
                ->addOrderBy('cro.careGroup', 'ASC');

            if ($typeId) {
                $queryBuilder
                    ->andWhere('reg.id = :id')
                    ->setParameter('id', $typeId);
            }
        } else {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    f.id as typeId,
                    fr.number as roomNumber,
                    fb.number as bedNumber,
                    f.name as name,
                    cfo.careGroup
                ')
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
                    ContractFacilityOption::class,
                    'cfo',
                    Join::WITH,
                    'cfo.contract = c'
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
                )
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE)
                ->addOrderBy('fr.number', 'ASC')
                ->addOrderBy('fb.number', 'ASC')
                ->addOrderBy('cfo.careGroup', 'ASC');

            if ($typeId) {
                $queryBuilder
                    ->andWhere('f.id = :id')
                    ->setParameter('id', $typeId);
            }
        }

        return $queryBuilder
            ->andWhere('c.type = :type')
            ->setParameter("type", $type)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param bool $typeId
     * @return array
     */
    public function getDietaryRestrictionsInfo($type, $typeId = false)
    {
        $queryBuilder = $this->createQueryBuilder('r');

        if ($type == \App\Model\Resident::TYPE_REGION) {
            $queryBuilder
                ->select('
                    r.id as id, 
                    sal.title as salutation, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    reg.id as typeId,
                    reg.name as name,
                    cro.careGroup,
                    diet.color as dietColor,
                    diet.title as dietTitle,
                    rd.description as dietDescription
                ')
                ->innerJoin(
                    Salutation::class,
                    'sal',
                    Join::WITH,
                    'r.salutation = sal'
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
                ->leftJoin(
                    ResidentDiet::class,
                    'rd',
                    Join::WITH,
                    'rd.resident = r'
                )
                ->leftJoin(
                    Diet::class,
                    'diet',
                    Join::WITH,
                    'rd.diet = diet'
                )
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE)
                ->addOrderBy('cro.careGroup', 'ASC');

            if ($typeId) {
                $queryBuilder
                    ->andWhere('reg.id = :id')
                    ->setParameter('id', $typeId);
            }
        } else {
            $queryBuilder
                ->select('
                    r.id as id, 
                    sal.title as salutation, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    f.id as typeId,
                    fr.number as roomNumber,
                    fb.number as bedNumber,
                    f.name as name,
                    cfo.careGroup,
                    dr.title as diningRoom,
                    diet.color as dietColor,
                    diet.title as dietTitle,
                    rd.description as dietDescription
                ')
                ->innerJoin(
                    Salutation::class,
                    'sal',
                    Join::WITH,
                    'r.salutation = sal'
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
                )
                ->leftJoin(
                    ResidentDiet::class,
                    'rd',
                    Join::WITH,
                    'rd.resident = r'
                )
                ->leftJoin(
                    Diet::class,
                    'diet',
                    Join::WITH,
                    'rd.diet = diet'
                )
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE)
                ->addOrderBy('fr.number', 'ASC')
                ->addOrderBy('fb.number', 'ASC')
                ->addOrderBy('cfo.careGroup', 'ASC');

            if ($typeId) {
                $queryBuilder
                    ->andWhere('f.id = :id')
                    ->setParameter('id', $typeId);
            }
        }

        return $queryBuilder
            ->andWhere('c.type = :type')
            ->setParameter("type", $type)
            ->groupBy('rd.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param bool $typeId
     * @return array
     */
    public function getNightActivityInfo($type, $typeId = false)
    {
        $queryBuilder = $this->createQueryBuilder('r');

        if ($type == \App\Model\Resident::TYPE_REGION) {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    reg.id as typeId,
                    reg.name as name,
                    cro.careGroup'
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
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE)
                ->addOrderBy('cro.careGroup', 'ASC');

            if ($typeId) {
                $queryBuilder
                    ->andWhere('reg.id = :id')
                    ->setParameter('id', $typeId);
            }
        } else {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    f.id as typeId,
                    fr.number as roomNumber,
                    fb.number as bedNumber,
                    f.name as name,
                    cfo.careGroup
                ')
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
                    ContractFacilityOption::class,
                    'cfo',
                    Join::WITH,
                    'cfo.contract = c'
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
                )
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE)
                ->addOrderBy('fr.number', 'ASC')
                ->addOrderBy('fb.number', 'ASC')
                ->addOrderBy('cfo.careGroup', 'ASC');

            if ($typeId) {
                $queryBuilder
                    ->andWhere('f.id = :id')
                    ->setParameter('id', $typeId);
            }
        }

        return $queryBuilder
            ->andWhere('c.type = :type')
            ->setParameter("type", $type)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param bool $typeId
     * @return array
     */
    public function getRoomAuditInfo($type, $typeId = false)
    {
        $queryBuilder = $this->createQueryBuilder('r');

        if ($type == \App\Model\Resident::TYPE_REGION) {
            $queryBuilder
                ->select('
                    r.id as id, 
                    sal.title as salutation, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    reg.id as typeId,
                    reg.name as name
                ')
                ->innerJoin(
                    Salutation::class,
                    'sal',
                    Join::WITH,
                    'r.salutation = sal'
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
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE);

            if ($typeId) {
                $queryBuilder
                    ->andWhere('reg.id = :id')
                    ->setParameter('id', $typeId);
            }
        } else {
            $queryBuilder
                ->select('
                    r.id as id, 
                    sal.title as salutation, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    f.id as typeId,
                    fr.number as roomNumber,
                    fb.number as bedNumber,
                    f.name as name
                ')
                ->innerJoin(
                    Salutation::class,
                    'sal',
                    Join::WITH,
                    'r.salutation = sal'
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
                )
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE);

            if ($typeId) {
                $queryBuilder
                    ->andWhere('f.id = :id')
                    ->setParameter('id', $typeId);
            }
        }

        return $queryBuilder
            ->andWhere('c.type = :type')
            ->setParameter("type", $type)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param bool $typeId
     * @param bool $residentId
     * @return mixed
     */
    public function getShowerSkinInspectionInfo($type, $typeId = false, $residentId = false)
    {
        /**
         * @var Resident $resident
         */
        $queryBuilder = $this->createQueryBuilder('r');

        if ($residentId) {
            $resident = $this->find($residentId);

            if (!$resident) {
                throw new ResidentNotFoundException();
            }
        }

        if ($type == \App\Model\Resident::TYPE_REGION) {
            $queryBuilder
                ->select('
                    r.id as id, 
                    sal.title as salutation, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    reg.id as typeId,
                    reg.name as name
                ')
                ->innerJoin(
                    Salutation::class,
                    'sal',
                    Join::WITH,
                    'r.salutation = sal'
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
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE);

            if ($typeId) {
                $queryBuilder
                    ->andWhere('reg.id = :id')
                    ->setParameter('id', $typeId);
            }
        } else {
            $queryBuilder
                ->select('
                    r.id as id, 
                    sal.title as salutation, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    f.id as typeId,
                    fr.number as roomNumber,
                    fb.number as bedNumber,
                    f.name as name
                ')
                ->innerJoin(
                    Salutation::class,
                    'sal',
                    Join::WITH,
                    'r.salutation = sal'
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
                )
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE);

            if ($typeId) {
                $queryBuilder
                    ->andWhere('f.id = :id')
                    ->setParameter('id', $typeId);
            }
        }

        if ($residentId) {
            return $queryBuilder
                ->andWhere('r.id = :id')
                ->setParameter("id", $residentId)
                ->getQuery()
                ->getResult();
        }

        return $queryBuilder
            ->andWhere('c.type = :type')
            ->setParameter("type", $type)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param bool $typeId
     * @param bool $residentId
     * @return mixed
     */
    public function getResidentsInfoByTypeOrId($type, $typeId = false, $residentId = false)
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
     * @param bool $typeId
     * @return mixed
     */
    public function getResidentDetailedInfo($type, $typeId = false)
    {
        /**
         * @var ContractAction $contractAction
         */
        $queryBuilder = $this->createQueryBuilder('r');

        if ($type == \App\Model\Resident::TYPE_REGION) {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    sal.title as salutation, 
                    c.type as type,
                    reg.id as typeId,
                    ca.ambulatory as ambulatory,
                    reg.name as typeName,
                    p.id as physicianId,
                    p.firstName as physicianFirstName,
                    p.lastName as physicianLastName,
                    p.address_1 as physicianAddress,
                    p.officePhone as physicianOfficePhone,
                    p.emergencyPhone as physicianEmergencyPhone,
                    p.fax as physicianFax,
                    pcsz.stateFull as physicianState,
                    pcsz.zipMain as physicianZip,
                    pcsz.city as physicianCity
                ')
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
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE);

            if ($typeId) {
                $queryBuilder
                    ->andWhere('reg.id = :id')
                    ->setParameter('id', $typeId);
            }
        } elseif ($type == \App\Model\Resident::TYPE_APARTMENT) {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    sal.title as salutation, 
                    c.type as type,
                    a.id as typeId,
                    a.name as typeName,
                    ca.ambulatory as ambulatory,
                    ar.number as roomNumber,
                    ab.number as bedNumber,
                    p.id as physicianId,
                    p.firstName as physicianFirstName,
                    p.lastName as physicianLastName,
                    p.address_1 as physicianAddress,
                    p.officePhone as physicianOfficePhone,
                    p.emergencyPhone as physicianEmergencyPhone,
                    p.fax as physicianFax,
                    pcsz.stateFull as physicianState,
                    pcsz.zipMain as physicianZip,
                    pcsz.city as physicianCity
                ')
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
                )
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE);

            if ($typeId) {
                $queryBuilder
                    ->andWhere('f.id = :id')
                    ->setParameter('id', $typeId);
            }
        } else {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    sal.title as salutation, 
                    c.type as type,
                    f.id as typeId,
                    f.name as typeName,
                    ca.ambulatory as ambulatory,
                    fr.number as roomNumber,
                    fb.number as bedNumber,
                    p.id as physicianId,
                    p.firstName as physicianFirstName,
                    p.lastName as physicianLastName,
                    p.address_1 as physicianAddress,
                    p.officePhone as physicianOfficePhone,
                    p.emergencyPhone as physicianEmergencyPhone,
                    p.fax as physicianFax,
                    pcsz.stateFull as physicianState,
                    pcsz.zipMain as physicianZip,
                    pcsz.city as physicianCity
                ')
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
                    ContractFacilityOption::class,
                    'cfo',
                    Join::WITH,
                    'cfo.contract = c'
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
                )
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE);

            if ($typeId) {
                $queryBuilder
                    ->andWhere('f.id = :id')
                    ->setParameter('id', $typeId);
            }
        }

        $queryBuilder
            ->innerJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'r.salutation = sal'
            )
            ->innerJoin(
                ResidentPhysician::class,
                'rf',
                Join::WITH,
                'rf.resident = r'
            )
            ->innerJoin(
                Physician::class,
                'p',
                Join::WITH,
                'rf.physician = p'
            )
            ->leftJoin(
                CityStateZip::class,
                'pcsz',
                Join::WITH,
                'p.csz = pcsz'
            );

        return $queryBuilder
            ->andWhere('c.type = :type')
            ->setParameter("type", $type)
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
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param $type
     * @param bool $typeId
     * @return mixed
     */
    public function getResidentContracts(\DateTime $startDate, \DateTime $endDate, $type, $typeId = false)
    {
        /**
         * @var ContractAction $contractAction
         */
        $queryBuilder = $this->createQueryBuilder('r');

        if ($type == \App\Model\Resident::TYPE_REGION) {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    sal.title as salutation, 
                    c.type as type,
                    reg.id as typeId,
                    reg.name as typeName,
                    ca.start as startDate,
                    ca.end as endDate,
                    cro.careGroup as careGroup,
                    cl.id as careLevelId,
                    cl.title as careLevelTitle,
                    rel.title as relationship,
                    rp.id as responsiblePersonId,
                    rp.firstName as responsiblePersonFirstName,
                    rp.lastName as responsiblePersonLastName,
                    rp.email as responsiblePersonEmail,
                    rpp.number as responsiblePersonPhoneNumber,
                    rpp.type as responsiblePersonPhoneType
                ')
                ->innerJoin(Contract::class,'c')
                ->innerJoin(ContractAction::class,'ca')
                ->innerJoin(ContractRegionOption::class,'cro')
                ->innerJoin(Region::class,'reg')
                ->innerJoin(CareLevel::class,'cl', Join::WITH, 'cro.careLevel = cl')
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE);

            if ($typeId) {
                $queryBuilder
                    ->andWhere('reg.id = :id')
                    ->setParameter('id', $typeId);
            }

            $queryBuilder->groupBy('cro.id');
        } elseif ($type == \App\Model\Resident::TYPE_APARTMENT) {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    sal.title as salutation, 
                    c.type as type,
                    a.id as typeId,
                    a.name as typeName,
                    ca.start as startDate,
                    ca.end as endDate,
                    rel.title as relationship,
                    rp.id as responsiblePersonId,
                    rp.firstName as responsiblePersonFirstName,
                    rp.lastName as responsiblePersonLastName,
                    rp.email as responsiblePersonEmail,
                    rpp.number as responsiblePersonPhoneNumber,
                    rpp.type as responsiblePersonPhoneType
                ')
                ->innerJoin(Contract::class,'c')
                ->innerJoin(ContractAction::class,'ca')
                ->innerJoin(ContractApartmentOption::class,'cao')
                ->innerJoin(ApartmentBed::class,'ab')
                ->innerJoin(ApartmentRoom::class,'ar')
                ->innerJoin(Apartment::class,'a')
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE);

            if ($typeId) {
                $queryBuilder
                    ->andWhere('f.id = :id')
                    ->setParameter('id', $typeId);
            }

            $queryBuilder->groupBy('cao.id');
        } else {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    sal.title as salutation, 
                    c.type as type,
                    f.id as typeId,
                    f.name as typeName,
                    ca.start as startDate,
                    ca.end as endDate,
                    cfo.careGroup as careGroup,
                    cl.id as careLevelId,
                    cl.title as careLevelTitle,
                    rel.title as relationship,
                    rp.id as responsiblePersonId,
                    rp.firstName as responsiblePersonFirstName,
                    rp.lastName as responsiblePersonLastName,
                    rp.email as responsiblePersonEmail,
                    rpp.number as responsiblePersonPhoneNumber,
                    rpp.type as responsiblePersonPhoneType
                ')
                ->innerJoin(Contract::class,'c')
                ->innerJoin(ContractAction::class,'ca')
                ->innerJoin(ContractFacilityOption::class,'cfo')
                ->innerJoin(FacilityBed::class,'fb')
                ->innerJoin(FacilityRoom::class,'fr')
                ->innerJoin(Facility::class,'f')
                ->innerJoin(CareLevel::class,'cl', Join::WITH, 'cfo.careLevel = cl')
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE);

            if ($typeId) {
                $queryBuilder
                    ->andWhere('f.id = :id')
                    ->setParameter('id', $typeId);
            }

            $queryBuilder->groupBy('cfo.id');
        }

        return $queryBuilder
            ->innerJoin(Salutation::class,'sal')
            ->leftJoin(ResidentResponsiblePerson::class,'rrp', Join::WITH, 'rrp.resident = r')
            ->leftJoin(ResponsiblePerson::class,'rp', Join::WITH, 'rrp.responsiblePerson = rp')
            ->leftJoin(Relationship::class,'rel', Join::WITH, 'rrp.relationship = rel')
            ->leftJoin(ResponsiblePersonPhone::class,'rpp', Join::WITH, 'rpp.responsiblePerson = rp AND rpp.primary = 1')
            ->andWhere('c.type = :type AND ca.start <= :endDate AND (ca.end IS NULL OR ca.end >= :startDate)')
            ->setParameter("type", $type)
            ->setParameter("startDate", $startDate->format('Y-m-d'))
            ->setParameter("endDate", $endDate->format('Y-m-d'))
            ->addOrderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param bool $typeId
     * @param bool $residentId
     * @return mixed
     */
    public function getResidentsFullInfoByTypeOrId($type, $typeId = false, $residentId = false)
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
