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
     * @param $type
     * @param null $typeId
     * @return mixed
     */
    public function getPhysicianSimpleReport($type, $typeId = null)
    {
        $physicianTable               = $this->getClassMetadata()->getTableName();
        $residentPhysicianTable       = $this->_em->getClassMetadata(ResidentPhysician::class)->getTableName();
        $contractTable                = $this->_em->getClassMetadata(Contract::class)->getTableName();
        $contractActionTable          = $this->_em->getClassMetadata(ContractAction::class)->getTableName();
        $facilityBedTable             = $this->_em->getClassMetadata(FacilityBed::class)->getTableName();
        $facilityRoomTable            = $this->_em->getClassMetadata(FacilityRoom::class)->getTableName();
        $facilityTable                = $this->_em->getClassMetadata(Facility::class)->getTableName();
        $apartmentBedTable            = $this->_em->getClassMetadata(ApartmentBed::class)->getTableName();
        $apartmentRoomTable           = $this->_em->getClassMetadata(ApartmentRoom::class)->getTableName();
        $apartmentTable               = $this->_em->getClassMetadata(Apartment::class)->getTableName();
        $regionTable                  = $this->_em->getClassMetadata(Region::class)->getTableName();

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Physician::class , 'p');
        $rsm->addFieldResult('p', 'id', 'id');
        $rsm->addFieldResult('p', 'first_name', 'firstName');
        $rsm->addFieldResult('p', 'last_name', 'lastName');
        $rsm->addScalarResult('typeId', 'typeId');
        $rsm->addScalarResult('typeName', 'typeName');
        $rsm->addScalarResult('residentCount', 'residentCount');

        if ($type == \App\Model\Resident::TYPE_FACILITY) {
            $sql = "SELECT
                    p.id,
                    p.first_name,
                    p.last_name,
                    f.name AS typeName,
                    COUNT(rp.id) AS residentCount,
                    fr.id_facility AS typeId
                FROM ". $physicianTable ." p
                  INNER JOIN ". $residentPhysicianTable ." rp on p.id = rp.id_physician
                  INNER JOIN ". $contractTable ." c on c.id_resident = rp.id_resident
                  INNER JOIN ". $contractActionTable ." ca on ca.id_contract = c.id
                  INNER JOIN ". $facilityBedTable ." fb on ca.id_facility_bed = fb.id
                  INNER JOIN ". $facilityRoomTable ." fr on fb.id_facility_room = fr.id
                  INNER JOIN ". $facilityTable ." f on f.id = fr.id_facility";

            if ($typeId) {
                $sql .= " WHERE fr.id_facility = :id_facility ";
            }

            $sql .= " GROUP BY p.id";

            $query = $this->_em->createNativeQuery($sql, $rsm);

            if ($typeId) {
                $query->setParameter('id_facility', $typeId);
            }
        } elseif ($type == \App\Model\Resident::TYPE_APARTMENT) {
            $sql = "SELECT
                    p.id,
                    p.first_name,
                    p.last_name,
                    a.name AS typeName,
                    COUNT(rp.id) AS residentCount,
                    ar.id_apartment AS typeId
                FROM ". $physicianTable ." p
                  INNER JOIN ". $residentPhysicianTable ." rp on p.id = rp.id_physician
                  INNER JOIN ". $contractTable ." c on c.id_resident = rp.id_resident
                  INNER JOIN ". $contractActionTable ." ca on ca.id_contract = c.id
                  INNER JOIN ". $apartmentBedTable ." ab on ca.id_apartment_bed = ab.id
                  INNER JOIN ". $apartmentRoomTable ." ar on ab.id_apartment_room = ar.id
                  INNER JOIN ". $apartmentTable ." a on a.id = ar.id_apartment
                WHERE ar.id_apartment = :id_apartment";

            if ($typeId) {
                $sql .= " WHERE ar.id_apartment = :id_apartment ";
            }

            $sql .= " GROUP BY p.id";

            $query = $this->_em->createNativeQuery($sql, $rsm);

            if ($typeId) {
                $query->setParameter('id_apartment', $typeId);
            }
        } else {
            $sql = "SELECT
                    p.id,
                    p.first_name,
                    p.last_name,
                    r.name AS typeName,
                    COUNT(rp.id) AS residentCount,
                    rr.id_region AS typeId
                FROM ". $physicianTable ." p
                  INNER JOIN ". $residentPhysicianTable ." rp on p.id = rp.id_physician
                  INNER JOIN ". $contractTable ." c on c.id_resident = rp.id_resident
                  INNER JOIN ". $contractActionTable ." ca on ca.id_contract = c.id
                  INNER JOIN ". $regionTable ." r on r.id = ca.id_region
                WHERE rr.id_region = :id_region
                GROUP BY p.id";

            if ($typeId) {
                $sql .= " WHERE rr.id_region = :id_region ";
            }

            $sql .= "GROUP BY p.id";

            $query = $this->_em->createNativeQuery($sql, $rsm);

            if ($typeId) {
                $query->setParameter('id_region', $typeId);
            }
        }

        return $query->getResult();
    }

    /**
     * @param $type
     * @param null $typeId
     * @return mixed
     */
    public function getPhysicianFullReport($type, $typeId = null)
    {
        $queryBuilder = $this->createQueryBuilder('p');

        if ($type == \App\Model\Resident::TYPE_REGION) {
            $queryBuilder
                ->select('
                    p.id as id, 
                    sal.title as salutation, 
                    p.firstName as firstName, 
                    p.lastName as lastName,
                    p.address_1 as address,
                    p.officePhone as officePhone,
                    p.emergencyPhone as emergencyPhone,
                    p.email as email,
                    p.fax as fax,
                    p.websiteUrl as websiteUrl,
                    reg.id as typeId,
                    c.type as type,
                    reg.name as name,
                    csz.stateFull as state,
                    csz.zipMain as zip,
                    csz.city as city,
                    COUNT(rf.id) as residentCount
                ')
                ->innerJoin(
                    Salutation::class,
                    'sal',
                    Join::WITH,
                    'p.salutation = sal'
                )
                ->innerJoin(
                    ResidentPhysician::class,
                    'rf',
                    Join::WITH,
                    'rf.physician = p'
                )
                ->innerJoin(
                    Contract::class,
                    'c',
                    Join::WITH,
                    'c.resident = rf.resident'
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
                    CityStateZip::class,
                    'csz',
                    Join::WITH,
                    'p.csz = csz'
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
                    p.id as id, 
                    sal.title as salutation, 
                    p.firstName as firstName, 
                    p.lastName as lastName,
                    p.address_1 as address,
                    p.officePhone as officePhone,
                    p.emergencyPhone as emergencyPhone,
                    p.email as email,
                    p.fax as fax,
                    p.websiteUrl as websiteUrl,
                    ap.id as typeId,
                    c.type as type,
                    ap.name as name,
                    csz.stateFull as state,
                    csz.zipMain as zip,
                    csz.city as city,
                    COUNT(rf.id) as residentCount
                ')
                ->innerJoin(
                    Salutation::class,
                    'sal',
                    Join::WITH,
                    'p.salutation = sal'
                )
                ->innerJoin(
                    ResidentPhysician::class,
                    'rf',
                    Join::WITH,
                    'rf.physician = p'
                )
                ->innerJoin(
                    Contract::class,
                    'c',
                    Join::WITH,
                    'c.resident = rf.resident'
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
                    'ap',
                    Join::WITH,
                    'ar.apartment = ap'
                )
                ->leftJoin(
                    CityStateZip::class,
                    'csz',
                    Join::WITH,
                    'p.csz = csz'
                )
                ->where('ca.state=:state AND ca.end IS NULL')
                ->setParameter('state', ContractState::ACTIVE);

            if ($typeId) {
                $queryBuilder
                    ->andWhere('ap.id = :id')
                    ->setParameter('id', $typeId);
            }
        } else {
            $queryBuilder
                ->select('
                    p.id as id, 
                    sal.title as salutation, 
                    p.firstName as firstName, 
                    p.lastName as lastName,
                    p.address_1 as address,
                    p.officePhone as officePhone,
                    p.emergencyPhone as emergencyPhone,
                    p.email as email,
                    p.fax as fax,
                    p.websiteUrl as websiteUrl,
                    f.id as typeId,
                    c.type as type,
                    f.name as name,
                    csz.stateFull as state,
                    csz.zipMain as zip,
                    csz.city as city,
                    COUNT(rf.id) as residentCount
                ')
                ->innerJoin(
                    Salutation::class,
                    'sal',
                    Join::WITH,
                    'p.salutation = sal'
                )
                ->innerJoin(
                    ResidentPhysician::class,
                    'rf',
                    Join::WITH,
                    'rf.physician = p'
                )
                ->innerJoin(
                    Contract::class,
                    'c',
                    Join::WITH,
                    'c.resident = rf.resident'
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
                ->leftJoin(
                    CityStateZip::class,
                    'csz',
                    Join::WITH,
                    'p.csz = csz'
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
