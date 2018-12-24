<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\Assessment\Assessment;
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
use App\Entity\Medication;
use App\Entity\Physician;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentDiet;
use App\Entity\ResidentMedication;
use App\Entity\ResidentPhysician;
use App\Entity\Salutation;
use App\Entity\Space;
use App\Model\ContractState;
use App\Model\ContractType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnexpectedResultException;

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
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
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

            $type = $resident->getType();
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
        $queryBuilder = $this->createQueryBuilder('r');

        if ($residentId) {
            $contractAction = $this->_em->getRepository(ContractAction::class)->getActiveByResident($residentId);

            if (is_null($contractAction)) {
                throw new ResidentNotFoundException();
            }

            $type = $contractAction->getContract()->getType();
        }

        if ($type == \App\Model\Resident::TYPE_REGION) {
            $queryBuilder
                ->select('
                    r.id as id, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    c.type as type,
                    ca.dnr as dnr,
                    r.birthday as birthday,
                    cro.address as address,
                    csz.city as city,
                    csz.state as state,
                    csz.zip as zip,
                    sal.title as salutation, 
                    reg.id as typeId,
                    reg.name as typeName
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
                ->innerJoin(
                    Salutation::class,
                    'sal',
                    Join::WITH,
                    'r.salutation = sal'
                )
                ->innerJoin(
                    CityStateZip::class,
                    'csz',
                    Join::WITH,
                    'cro.csz = csz'
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
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    r.birthday as birthday,
                    c.type as type,
                    ca.dnr as dnr,
                    sal.title as salutation, 
                    f.id as typeId,
                    f.name as typeName,
                    f.address as address,
                    f.license as license,
                    fr.number as roomNumber,
                    fr.floor as floor,
                    fb.number as bedNumber
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
                ->innerJoin(
                    Salutation::class,
                    'sal',
                    Join::WITH,
                    'r.salutation = sal'
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
}
