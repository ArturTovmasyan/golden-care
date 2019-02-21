<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\CareLevel;
use App\Entity\Contract;
use App\Entity\ContractAction;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\Space;
use App\Model\ContractState;
use App\Model\ContractType;
use App\Util\Common\ImtDateTimeInterval;
use Doctrine\ORM\AbstractQuery;
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
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(ContractAction::class, 'ca')
            ->innerJoin(
                Contract::class,
                'c',
                Join::WITH,
                'c = ca.contract'
            );

        if ($space !== null) {
            $queryBuilder
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
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('ca.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('ca.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $ids
     * @return mixed
     */
    public function findByIds(Space $space = null, array $entityGrants = null, $ids)
    {
        $qb = $this
            ->createQueryBuilder('ca')
            ->where('ca.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Contract::class,
                    'c',
                    Join::WITH,
                    'c = ca.contract'
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
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ca.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('ca.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @return mixed
     */
    public function getContractLastAction(Space $space = null, array $entityGrants = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('ca')
            ->join('ca.contract', 'c')
            ->where('c.id=:id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
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
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ca.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('ca.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @return mixed
     */
    public function getActiveByResident(Space $space = null, array $entityGrants = null, $id)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->select('ca, c')
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->where('ca.state=:state AND ca.end IS NULL')
            ->andWhere('r.id=:id')
            ->setParameter('id', $id)
            ->setParameter('state', ContractState::ACTIVE);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ca.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param $id
     * @return mixed
     */
    public function getDataByResident(Space $space = null, array $entityGrants = null, $type, $id)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->select('ca, c')
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->where('ca.state=:state AND ca.end IS NULL')
            ->andWhere('r.id=:id')
            ->andWhere('c.type=:type')
            ->setParameter('type', $type)
            ->setParameter('id', $id)
            ->setParameter('state', ContractState::ACTIVE);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ca.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect('o')
                    ->join('c.contractFacilityOption', 'o');
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->addSelect('o')
                    ->join('c.contractApartmentOption', 'o');
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->addSelect('o')
                    ->join('c.contractRegionOption', 'o');
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param $id
     * @return mixed
     */
    public function getResidentByBed(Space $space = null, array $entityGrants = null, $type, $id)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->select('r.id AS residentId')
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->where('ca.state=:state AND ca.end IS NULL')
            ->andWhere('c.type=:type')
            ->setParameter('type', $type)
            ->setParameter('state', ContractState::ACTIVE);

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->join('ca.facilityBed', 'fb')
                    ->andWhere('fb.id=:id')
                    ->setParameter('id', $id);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            FacilityRoom::class,
                            'fr',
                            Join::WITH,
                            'fr = fb.room'
                        )
                        ->innerJoin(
                            Facility::class,
                            'f',
                            Join::WITH,
                            'f = fr.facility'
                        )
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = f.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->join('ca.apartmentBed', 'ab')
                    ->andWhere('ab.id=:id')
                    ->setParameter('id', $id);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            ApartmentRoom::class,
                            'ar',
                            Join::WITH,
                            'ar = ab.room'
                        )
                        ->innerJoin(
                            Apartment::class,
                            'a',
                            Join::WITH,
                            'a = ar.apartment'
                        )
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = a.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->join('ca.region', 'reg')
                    ->andWhere('reg.id=:id')
                    ->setParameter('id', $id);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = reg.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ca.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param $ids
     * @return mixed
     */
    public function getResidentsByBeds(Space $space = null, array $entityGrants = null, $type, $ids)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->select(
                'ca AS action',
                'c AS contract',
                'r AS resident'
            )
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->where('ca.state=:state AND ca.end IS NULL')
            ->andWhere('c.type=:type')
            ->setParameter('type', $type)
            ->setParameter('state', ContractState::ACTIVE);

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect('fb.id AS bedId')
                    ->join('ca.facilityBed', 'fb')
                    ->andWhere('fb.id IN (:ids)')
                    ->setParameter('ids', $ids);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            FacilityRoom::class,
                            'fr',
                            Join::WITH,
                            'fr = fb.room'
                        )
                        ->innerJoin(
                            Facility::class,
                            'f',
                            Join::WITH,
                            'f = fr.facility'
                        )
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = f.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->addSelect('ab.id AS bedId')
                    ->join('ca.apartmentBed', 'ab')
                    ->andWhere('ab.id IN (:ids)')
                    ->setParameter('ids', $ids);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            ApartmentRoom::class,
                            'ar',
                            Join::WITH,
                            'ar = ab.room'
                        )
                        ->innerJoin(
                            Apartment::class,
                            'a',
                            Join::WITH,
                            'a = ar.apartment'
                        )
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = a.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->addSelect('reg.id AS regionId')
                    ->join('ca.region', 'reg')
                    ->andWhere('reg.id IN (:ids)')
                    ->setParameter('ids', $ids);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = reg.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ca.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param $ids
     * @return mixed
     */
    public function getBeds(Space $space = null, array $entityGrants = null, $type, $ids)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->where('ca.state=:state AND ca.end IS NULL')
            ->andWhere('c.type=:type')
            ->setParameter('type', $type)
            ->setParameter('state', ContractState::ACTIVE);

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->select('fb.id AS bedId')
                    ->join('ca.facilityBed', 'fb')
                    ->andWhere('fb.id IN (:ids)')
                    ->setParameter('ids', $ids);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            FacilityRoom::class,
                            'fr',
                            Join::WITH,
                            'fr = fb.room'
                        )
                        ->innerJoin(
                            Facility::class,
                            'f',
                            Join::WITH,
                            'f = fr.facility'
                        )
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = f.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->select('ab.id AS bedId')
                    ->join('ca.apartmentBed', 'ab')
                    ->andWhere('ab.id IN (:ids)')
                    ->setParameter('ids', $ids);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            ApartmentRoom::class,
                            'ar',
                            Join::WITH,
                            'ar = ab.room'
                        )
                        ->innerJoin(
                            Apartment::class,
                            'a',
                            Join::WITH,
                            'a = ar.apartment'
                        )
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = a.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->select('reg.id AS regionId')
                    ->join('ca.region', 'reg')
                    ->andWhere('reg.id IN (:ids)')
                    ->setParameter('ids', $ids);

                if ($space !== null) {
                    $qb
                        ->innerJoin(
                            Space::class,
                            's',
                            Join::WITH,
                            's = reg.space'
                        )
                        ->andWhere('s = :space')
                        ->setParameter('space', $space);
                }
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ca.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param $id
     * @return mixed
     */
    public function getActiveResidentsByStrategy(Space $space = null, array $entityGrants = null, $type, $id)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->select(
                'r.id AS id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'rs.title AS salutation'
            )
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->leftJoin('r.salutation', 'rs')
            ->where('ca.state=:state AND ca.end IS NULL')
            ->andWhere('c.type=:type')
            ->setParameter('type', $type)
            ->setParameter('state', ContractState::ACTIVE);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ca.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'fbr.number AS room_number',
                        'fb.number AS bed_number'
                    )
                    ->join('ca.facilityBed', 'fb')
                    ->join('fb.room', 'fbr')
                    ->join('fbr.facility', 'fbrf')
                    ->andWhere('fbrf.id=:id')
                    ->setParameter('id', $id);
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->addSelect(
                        'abr.number AS room_number',
                        'ab.number AS bed_number'
                    )
                    ->join('ca.apartmentBed', 'ab')
                    ->join('ab.room', 'abr')
                    ->join('abr.apartment', 'abra')
                    ->andWhere('abra.id=:id')
                    ->setParameter('id', $id);
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->join('ca.region', 'reg')
                    ->andWhere('reg.id=:id')
                    ->setParameter('id', $id);
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param $id
     * @return mixed
     */
    public function getInactiveResidentsByStrategy(Space $space = null, array $entityGrants = null, $type, $id)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->select(
                'MAX(ca.id) AS HIDDEN caId',
                'r.id AS id',
                'r.firstName AS first_name',
                'r.lastName AS last_name',
                'rs.title AS salutation'
            )
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->leftJoin('r.salutation', 'rs')
            ->where('ca.end IS NOT NULL')
            ->andWhere('c.type=:type')
            ->setParameter('type', $type);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ca.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'fbr.number AS room_number',
                        'fb.number AS bed_number'
                    )
                    ->join('c.contractFacilityOption', 'o')
                    ->join('o.facilityBed', 'fb')
                    ->join('fb.room', 'fbr')
                    ->join('fbr.facility', 'fbrf')
                    ->andWhere('fbrf.id=:id')
                    ->andWhere('o.state=:state')
                    ->setParameter('id', $id)
                    ->setParameter('state', ContractState::TERMINATED);
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->addSelect(
                        'abr.number AS room_number',
                        'ab.number AS bed_number'
                    )
                    ->join('c.contractApartmentOption', 'o')
                    ->join('o.apartmentBed', 'ab')
                    ->join('ab.room', 'abr')
                    ->join('abr.apartment', 'abra')
                    ->andWhere('abra.id=:id')
                    ->andWhere('o.state=:state')
                    ->setParameter('id', $id)
                    ->setParameter('state', ContractState::TERMINATED);
                break;
            case ContractType::TYPE_REGION:
                $qb
                    ->join('c.contractRegionOption', 'o')
                    ->join('o.region', 'reg')
                    ->andWhere('reg.id=:id')
                    ->andWhere('o.state=:state')
                    ->setParameter('id', $id)
                    ->setParameter('state', ContractState::TERMINATED);
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        $qb
            ->andWhere('ca.id IN (SELECT MAX(lca.id) FROM App:ContractAction lca JOIN lca.contract lc JOIN lc.resident lr WHERE lca.state='. ContractState::TERMINATED .' AND lca.end IS NOT NULL GROUP BY lr.id)')
            ->andWhere('r.id NOT IN (SELECT ar.id FROM App:ContractAction aca JOIN aca.contract ac JOIN ac.resident ar WHERE aca.state='. ContractState::ACTIVE .' AND aca.end IS NULL)')
            ->groupBy('r.id');

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param $type
     * @param $ids
     * @return mixed
     */
    public function getBedIdAndTypeId(Space $space = null, $type, $ids)
    {
        $qb = $this->createQueryBuilder('ca');

        $qb
            ->join('ca.contract', 'c')
            ->join('c.resident', 'r')
            ->where('ca.state=:state AND ca.end IS NULL')
            ->andWhere('c.type=:type')
            ->setParameter('type', $type)
            ->setParameter('state', ContractState::ACTIVE);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->select('
                        fb.id AS bedId,
                        type.id AS typeId
                    ')
                    ->join('ca.facilityBed', 'fb')
                    ->join('fb.room', 'room')
                    ->join('room.facility', 'type')
                    ->andWhere('fb.id IN (:ids)')
                    ->setParameter('ids', $ids);
                break;
            case ContractType::TYPE_APARTMENT:
                $qb
                    ->select('
                        ab.id AS bedId,
                        type.id AS typeId
                    ')
                    ->join('ca.apartmentBed', 'ab')
                    ->join('ab.room', 'room')
                    ->join('room.apartment', 'type')
                    ->andWhere('ab.id IN (:ids)')
                    ->setParameter('ids', $ids);
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param ImtDateTimeInterval|null $dateTimeInterval
     * @return QueryBuilder
     */
    public function getContractActionIntervalQb(ImtDateTimeInterval $dateTimeInterval = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ca');
        if ($dateTimeInterval) {
            $qb
                ->join('ca.contract', 'cac')
                ->join('cac.resident', 'car')
                ->where('ca.end IS NULL OR ca.end > = :start')
                ->setParameter('start', $dateTimeInterval->getStart());
            if ($dateTimeInterval->getEnd()) {
                $qb
                    ->andWhere('ca.start < = :end')
                    ->setParameter('end', $dateTimeInterval->getEnd());
            }
        }
        return $qb;
    }

    /**
     * @param ImtDateTimeInterval $dateTimeInterval
     * @return QueryBuilder
     */
    public function getRoomListContractActionIntervalQb(ImtDateTimeInterval $dateTimeInterval): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ca');
        if ($dateTimeInterval) {
            $qb
                ->join('ca.contract', 'cac')
                ->join('cac.resident', 'car')
                ->andWhere('(ca.start < = :end AND ca.start > = :start) OR (ca.start < :start AND (ca.end IS NULL OR ca.end > :start))')
                ->setParameter('start', $dateTimeInterval->getStart())
                ->setParameter('end', $dateTimeInterval->getEnd());
        }
        return $qb;
    }

    /**
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param null $typeId
     * @return QueryBuilder
     */
    public function getContractActionReportQb($type, ImtDateTimeInterval $reportInterval = null, $typeId = null) : QueryBuilder
    {
        /** @var ContractActionRepository $actionRepo */
        $actionRepo = $this
            ->getEntityManager()
            ->getRepository(ContractAction::class);

        /** @var QueryBuilder $qb */
        $qb = $actionRepo
            ->getContractActionIntervalQb($reportInterval);

        $qb
            ->from(Resident::class, 'r')
            ->andWhere('r.id = car.id')
            ->andWhere('cac.type=:type')
            ->setParameter('type', $type)
            ->select(
                'r.id as id',
                'r.firstName as firstName',
                'r.lastName as lastName',
                'ca.id as actionId',
                'ca.start as admitted',
                'ca.end as discharged'
            );

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.shorthand as typeShorthand,
                        fr.number as roomNumber,
                        fb.number as bedNumber,
                        fb.id as bedId,
                        ca.careGroup as careGroup,
                        cl.title as careLevel'
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
                    ->innerJoin(
                        CareLevel::class,
                        'cl',
                        Join::WITH,
                        'ca.careLevel = cl'
                    );

                $qb
                    ->orderBy('f.shorthand')
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
                        ar.number as roomNumber,
                        ab.number as bedNumber
                        ab.id as bedId'
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

                $qb
                    ->orderBy('a.shorthand')
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
                        'reg.id as typeId,
                        reg.name as typeName,
                        reg.shorthand as typeShorthand,
                        ca.careGroup as careGroup,
                        cl.title as careLevel'
                    )
                    ->innerJoin(
                        Region::class,
                        'reg',
                        Join::WITH,
                        'ca.region = reg'
                    )
                    ->innerJoin(
                        CareLevel::class,
                        'cl',
                        Join::WITH,
                        'ca.careLevel = cl'
                    );

                $qb
                    ->orderBy('reg.shorthand');

                if ($typeId) {
                    $qb
                        ->andWhere('reg.id = :typeId')
                        ->setParameter('typeId', $typeId);
                }
                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        return $qb;
    }

    /**
     * @param Space|null $space
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param null $typeId
     * @return mixed
     */
    public function getResidents60DaysRosterData(Space $space = null, $type, ImtDateTimeInterval $reportInterval = null, $typeId = null)
    {
        $qb = $this
            ->getContractActionReportQb($type, $reportInterval, $typeId)
            ->andWhere('r.id IN (SELECT ar.id 
                        FROM App:ContractAction aca 
                        JOIN aca.contract ac 
                        JOIN ac.resident ar 
                        WHERE aca.state='. ContractState::ACTIVE .' AND aca.end IS NULL)'
            );

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }
}