<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\CareLevel;
use App\Entity\CityStateZip;
use App\Entity\ContractAction;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentRent;
use App\Entity\Space;
use App\Model\ContractState;
use App\Model\ContractType;
use App\Util\Common\ImtDateTimeInterval;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentRentRepository
 * @package App\Repository
 */
class ResidentRentRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentRent::class, 'rr')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rr.resident'
            );

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = r.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        $queryBuilder
            ->groupBy('rr.id');
    }

    /**
     * @param Space|null $space
     * @param $id
     * @return mixed
     */
    public function getBy(Space $space = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('rr')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rr.resident'
            )
            ->where('r.id = :id')
            ->setParameter('id', $id);

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
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param $id
     * @return mixed
     */
    public function getOne(Space $space = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('rr')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rr.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('rr.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param $ids
     * @return mixed
     */
    public function findByIds(Space $space = null, $ids)
    {
        $qb = $this->createQueryBuilder('rr');

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rr.resident'
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

        return $qb->where($qb->expr()->in('rr.id', $ids))
            ->groupBy('rr.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(Space $space = null, array $residentIds)
    {
        $qb = $this->createQueryBuilder('rr');

        $qb
            ->select('
                    rr.id as id,
                    rr.start as start,
                    rr.amount as amount,
                    r.id as residentId
            ')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'rr.resident = r'
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
            ->where($qb->expr()->in('r.id', $residentIds))
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) FROM App:ResidentRent mrr JOIN mrr.resident res GROUP BY res.id)')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param null $typeId
     * @return QueryBuilder
     */
    public function getContractActionWithRentQb($type, ImtDateTimeInterval $reportInterval = null, $typeId = null)
    {
        /** @var QueryBuilder $qb */
        $qb = $this
            ->getEntityManager()
            ->getRepository(ContractAction::class)
            ->getContractActionIntervalQb($reportInterval);

        $qb
            ->from(ResidentRent::class, 'rr')
            ->from(Resident::class, 'r')
            ->andWhere('rr.resident = r')
            ->andWhere('rr.resident = car')
            ->andWhere('(rr.end IS NULL OR rr.end > = ca.start) AND (ca.end IS NULL OR rr.start < = ca.end)')
            ->andWhere('cac.type=:type')
            ->setParameter('type', $type)
            ->select(
                'r.id as id',
                'r.firstName as firstName',
                'r.lastName as lastName',
                'ca.id as actionId',
                'rr.id as rentId',
                'rr.amount as amount',
                'rr.period as period',
                '(CASE WHEN rr.start > = ca.start THEN rr.start ELSE ca.start END) as admitted',
                '(CASE
                    WHEN rr.end IS NULL AND ca.end IS NULL THEN ca.end
                    WHEN ca.end IS NULL THEN rr.end
                    WHEN rr.end IS NULL THEN ca.end
                    WHEN rr.end < ca.end THEN rr.end
                    ELSE ca.end END) as discharged',
                'rr.source as sources'
            );

        if ($reportInterval) {
            $qb
                ->andWhere('rr.end IS NULL OR rr.end > = :start');
            if ($reportInterval->getEnd()) {
                $qb
                    ->andWhere('rr.start < = :end');
            }
        }

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.shorthand as typeShorthand,
                        fr.number as roomNumber,
                        fb.number as bedNumber,
                        fb.id as bedId'
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
                        reg.shorthand as typeShorthand'
                    )
                    ->innerJoin(
                        Region::class,
                        'reg',
                        Join::WITH,
                        'ca.region = reg'
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
    public function getRentsWithSources(Space $space = null, $type, ImtDateTimeInterval $reportInterval = null, $typeId = null)
    {
        $qb = $this
            ->getContractActionWithRentQb($type, $reportInterval, $typeId)
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) 
                        FROM App:ResidentRent mrr 
                        JOIN mrr.resident res 
                        WHERE (mrr.end IS NULL OR mrr.end > = ca.start) AND (ca.end IS NULL OR mrr.start < = ca.end)
                        GROUP BY res.id)'
            );

        if ($reportInterval) {
            if ($reportInterval->getEnd()) {
                $qb
                    ->andWhere('ca.id IN (SELECT MAX(mca.id)
                        FROM App:ContractAction mca
                        JOIN mca.contract mcac
                        JOIN mcac.resident mcacr
                        WHERE (mca.end IS NULL OR mca.end > = :startDate) AND (mca.start < = :endDate)
                        GROUP BY mcacr.id)'
                    )
                    ->setParameter('startDate', $reportInterval->getStart())
                    ->setParameter('endDate', $reportInterval->getEnd());
            } else {
                $qb
                    ->andWhere('ca.id IN (SELECT MAX(mca.id)
                        FROM App:ContractAction mca
                        JOIN mca.contract mcac
                        JOIN mcac.resident mcacr
                        WHERE (mca.end IS NULL OR mca.end > = :startDate)
                        GROUP BY mcacr.id)'
                    )
                    ->setParameter('startDate', $reportInterval->getStart());
            }
        } else {
            $qb
                ->andWhere('ca.id IN (SELECT MAX(mca.id)
                    FROM App:ContractAction mca
                    JOIN mca.contract mcac
                    JOIN mcac.resident mcacr
                    GROUP BY mcacr.id)'
                );
        }

        $qb
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

    /**
     * @param Space|null $space
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param null $typeId
     * @return mixed
     */
    public function getRoomRentMasterNewData(Space $space = null, $type, ImtDateTimeInterval $reportInterval = null, $typeId = null)
    {
        $qb = $this
            ->getContractActionWithRentQb($type, $reportInterval, $typeId)
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) 
                        FROM App:ResidentRent mrr 
                        JOIN mrr.resident res 
                        WHERE (mrr.end IS NULL OR mrr.end > = ca.start) AND (ca.end IS NULL OR mrr.start < = ca.end)
                        GROUP BY res.id)'
            )
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

    /**
     * @param Space|null $space
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param null $typeId
     * @return mixed
     */
    public function getRoomRentData(Space $space = null, $type, ImtDateTimeInterval $reportInterval = null, $typeId = null)
    {
        $qb = $this
            ->getContractActionWithRentQb($type, $reportInterval, $typeId)
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) 
                        FROM App:ResidentRent mrr 
                        JOIN mrr.resident res 
                        WHERE (mrr.end IS NULL OR mrr.end > = ca.start) AND (ca.end IS NULL OR mrr.start < = ca.end)
                        GROUP BY res.id)'
            )
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

    /**
     * @param $type
     * @param ImtDateTimeInterval $reportInterval
     * @param null $typeId
     * @return QueryBuilder
     */
    public function getRoomListContractActionWithRentQb($type, ImtDateTimeInterval $reportInterval, $typeId = null)
    {
        /** @var QueryBuilder $qb */
        $qb = $this
            ->getEntityManager()
            ->getRepository(ContractAction::class)
            ->getRoomListContractActionIntervalQb($reportInterval);

        $qb
            ->from(ResidentRent::class, 'rr')
            ->from(Resident::class, 'r')
            ->andWhere('rr.resident = r')
            ->andWhere('rr.resident = car')
            ->andWhere('(rr.start < = :end AND rr.start > = :start) OR (rr.start < :start AND (rr.end IS NULL OR rr.end > :start))')
            ->andWhere('cac.type=:type')
            ->setParameter('type', $type)
            ->select(
                'r.id as id',
                'r.firstName as firstName',
                'r.lastName as lastName',
                'ca.id as actionId',
                'cac.start as admitted',
                'rr.id as rentId',
                'rr.amount as amount',
                'rr.period as period'
            );

        if ($reportInterval) {
            $qb
                ->andWhere('rr.end IS NULL OR rr.end > = :start');
            if ($reportInterval->getEnd()) {
                $qb
                    ->andWhere('rr.start < = :end');
            }
        }

        switch ($type) {
            case ContractType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.shorthand as typeShorthand,
                        fr.number as roomNumber,
                        fr.floor as roomFloor,
                        fb.number as bedNumber,
                        fb.id as bedId,
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
                        ar.floor as roomFloor,
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
                        ca.address as address,
                        csz.city as city,
                        csz.stateAbbr as state,
                        csz.zipMain as zip,
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
                    )
                    ->innerJoin(
                        CityStateZip::class,
                        'csz',
                        Join::WITH,
                        'ca.csz = csz'
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
     * @param ImtDateTimeInterval $reportInterval
     * @param null $typeId
     * @return mixed
     */
    public function getRoomListData(Space $space = null, $type, ImtDateTimeInterval $reportInterval, $typeId = null)
    {
        $qb = $this
            ->getRoomListContractActionWithRentQb($type, $reportInterval, $typeId)
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) 
                        FROM App:ResidentRent mrr 
                        JOIN mrr.resident res 
                        WHERE (mrr.start < = :endDate AND mrr.start > = :startDate) OR (mrr.start < :startDate AND (mrr.end IS NULL OR mrr.end > :startDate))
                        GROUP BY res.id)'
            )
            ->andWhere('ca.id IN (SELECT MAX(mca.id)
                        FROM App:ContractAction mca
                        JOIN mca.contract mcac
                        JOIN mcac.resident mcacr
                        WHERE (mca.start < = :endDate AND mca.start > = :startDate) OR (mca.start < :startDate AND (mca.end IS NULL OR mca.end > :startDate))
                        GROUP BY mcacr.id)'
            )
            ->andWhere('r.id IN (SELECT ar.id 
                        FROM App:ContractAction aca 
                        JOIN aca.contract ac 
                        JOIN ac.resident ar 
                        WHERE aca.state='. ContractState::ACTIVE .' AND aca.end IS NULL)'
            )
            ->setParameter('startDate', $reportInterval->getStart())
            ->setParameter('endDate', $reportInterval->getEnd());

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

    /**
     * @param Space|null $space
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param null $typeId
     * @return mixed
     */
    public function getRoomRentMasterData(Space $space = null, $type, ImtDateTimeInterval $reportInterval = null, $typeId = null)
    {
        $qb = $this
            ->getContractActionWithRentQb($type, $reportInterval, $typeId)
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) 
                        FROM App:ResidentRent mrr 
                        JOIN mrr.resident res 
                        WHERE (mrr.end IS NULL OR mrr.end > = ca.start) AND (ca.end IS NULL OR mrr.start < = ca.end)
                        GROUP BY res.id)'
            )
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