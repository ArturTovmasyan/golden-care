<?php

namespace App\Repository;

use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\Contract;
use App\Entity\ContractAction;
use App\Entity\ContractApartmentOption;
use App\Entity\ContractFacilityOption;
use App\Entity\ContractRegionOption;
use App\Entity\EventDefinition;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Physician;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentEvent;
use App\Entity\ResponsiblePerson;
use App\Entity\Salutation;
use App\Model\ContractState;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentEventRepository
 * @package App\Repository
 */
class ResidentEventRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentEvent::class, 're')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = re.resident'
            )
            ->leftJoin(
                EventDefinition::class,
                'ed',
                Join::WITH,
                'ed = re.definition'
            )
            ->leftJoin(
                Physician::class,
                'p',
                Join::WITH,
                'p = re.physician'
            )
            ->leftJoin(
                Salutation::class,
                'ps',
                Join::WITH,
                'ps = p.salutation'
            )
            ->leftJoin(
                ResponsiblePerson::class,
                'rp',
                Join::WITH,
                'rp = re.responsiblePerson'
            )
            ->leftJoin(
                Salutation::class,
                'rps',
                Join::WITH,
                'rps = rp.salutation'
            )
            ->groupBy('re.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('re');

        return $qb->where($qb->expr()->in('re.id', $ids))
            ->groupBy('re.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $type
     * @param bool $typeId
     * @return mixed
     */
    public function getByPeriodAndType(\DateTime $startDate, \DateTime $endDate, $type, $typeId = false)
    {
        $queryBuilder = $this->createQueryBuilder('re');

        if ($type == \App\Model\Resident::TYPE_REGION) {
            $queryBuilder
                ->select('
                    r.id as residentId, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    sal.title as salutation, 
                    c.type as type,
                    reg.id as typeId,
                    reg.name as typeName,
                    reg.shorthand as shorthand,
                    re.date as startDate,
                    re.additionalDate as endDate,
                    re.notes as notes,
                    ed.title as definitionTitle
                ')
                ->innerJoin(
                    EventDefinition::class,
                    'ed',
                    Join::WITH,
                    're.definition = ed'
                )
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    're.resident = r'
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
        } elseif ($type == \App\Model\Resident::TYPE_APARTMENT) {
            $queryBuilder
                ->select('
                    r.id as residentId, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    sal.title as salutation, 
                    c.type as type,
                    a.id as typeId,
                    a.name as typeName,
                    a.shorthand as shorthand,
                    ca.ambulatory as ambulatory,
                    ar.number as roomNumber,
                    ab.number as bedNumber,
                    re.date as startDate,
                    re.additionalDate as endDate,
                    re.notes as notes,
                    ed.title as definitionTitle
                ')
                ->innerJoin(
                    EventDefinition::class,
                    'ed',
                    Join::WITH,
                    're.definition = ed'
                )
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    're.resident = r'
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
                    r.id as residentId, 
                    r.firstName as firstName, 
                    r.lastName as lastName,
                    sal.title as salutation, 
                    c.type as type,
                    f.id as typeId,
                    f.name as typeName,
                    f.shorthand as shorthand,
                    ca.ambulatory as ambulatory,
                    fr.number as roomNumber,
                    fb.number as bedNumber,
                    re.date as startDate,
                    re.additionalDate as endDate,
                    re.notes as notes,
                    ed.title as definitionTitle
                ')
                ->innerJoin(
                    EventDefinition::class,
                    'ed',
                    Join::WITH,
                    're.definition = ed'
                )
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    're.resident = r'
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

        $startDate->setTime(00, 00);
        $endDate->setTime(23, 59);

        return $queryBuilder
            ->innerJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'r.salutation = sal'
            )
            ->andWhere('c.type = :type AND re.date >= :startDate AND re.date <= :endDate')
            ->setParameter("type", $type)
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->groupBy('re.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(array $residentIds)
    {
        $qb = $this->createQueryBuilder('re');

        return $qb
            ->select(
                're.id as id,
                    r.id as residentId,
                    ed.title as title,
                    re.date as date,
                    re.additionalDate as additionalDate,
                    re.notes as notes,
                    p.firstName as physicianFirstName,
                    p.lastName as physicianLastName,
                    psal.title as physicianSalutation,
                    rp.firstName as responsiblePersonFirstName,
                    rp.lastName as responsiblePersonLastName,
                    rpsal.title as responsiblePersonSalutation'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                're.resident = r'
            )
            ->innerJoin(
                EventDefinition::class,
                'ed',
                Join::WITH,
                're.definition = ed'
            )
            ->leftJoin(
                Physician::class,
                'p',
                Join::WITH,
                're.physician = p'
            )
            ->leftJoin(
                Salutation::class,
                'psal',
                Join::WITH,
                'p.salutation = psal'
            )
            ->leftJoin(
                ResponsiblePerson::class,
                'rp',
                Join::WITH,
                're.responsiblePerson = rp'
            )
            ->leftJoin(
                Salutation::class,
                'rpsal',
                Join::WITH,
                'rp.salutation = rpsal'
            )
            ->where($qb->expr()->in('r.id', $residentIds))
            ->orderBy('re.date', 'DESC')
            ->groupBy('re.id')
            ->getQuery()
            ->getResult();
    }
}