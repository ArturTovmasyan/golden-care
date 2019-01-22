<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\ContractAction;
use App\Entity\ContractApartmentOption;
use App\Entity\ContractFacilityOption;
use App\Entity\ContractRegionOption;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentRent;
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
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentRent::class, 'rr')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rr.resident'
            )
            ->groupBy('rr.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('rr');

        return $qb->where($qb->expr()->in('rr.id', $ids))
            ->groupBy('rr.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(array $residentIds)
    {
        $qb = $this->createQueryBuilder('rr');

        return $qb
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
            )
            ->where($qb->expr()->in('r.id', $residentIds))
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) FROM App:ResidentRent mrr JOIN mrr.resident res GROUP BY res.id)')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param bool $typeId
     * @return QueryBuilder
     */
    public function getContractActionWithRentQb($type, ImtDateTimeInterval $reportInterval = null, $typeId = false)
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
            ->andWhere('(rr.end IS NULL OR rr.end > =  ca.start) AND (ca.end IS NULL OR rr.start < =  ca.end)')
            ->andWhere('cac.type=:type')
            ->setParameter('type', $type)
            ->select(
                'r.id as id',
                'r.firstName as firstName',
                'r.lastName as lastName',
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
                        fr.number as roomNumber,
                        fb.number as bedNumber'
                    )
                    ->innerJoin(
                        ContractFacilityOption::class,
                        'cfo',
                        Join::WITH,
                        'cfo.contract = cac'
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
                        ar.number as roomNumber,
                        ab.number as bedNumber'
                    )
                    ->innerJoin(
                        ContractApartmentOption::class,
                        'cao',
                        Join::WITH,
                        'cao.contract = cac'
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
                        reg.name as typeName'
                    )
                    ->innerJoin(
                        ContractRegionOption::class,
                        'cro',
                        Join::WITH,
                        'cro.contract = cac'
                    )
                    ->innerJoin(
                        Region::class,
                        'reg',
                        Join::WITH,
                        'cro.region = reg'
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
     * @param $type
     * @param ImtDateTimeInterval|null $reportInterval
     * @param bool $typeId
     * @return mixed
     */
    public function getRentsWithSources($type, ImtDateTimeInterval $reportInterval = null, $typeId = false)
    {
        return $this
            ->getContractActionWithRentQb($type, $reportInterval, $typeId)
            ->andWhere('rr.id IN (SELECT MAX(mrr.id) FROM App:ResidentRent mrr JOIN mrr.resident res GROUP BY res.id)')
            ->andWhere('ca.id IN (SELECT MAX(mca.id) FROM App:ContractAction mca JOIN mca.contract mcac JOIN mcac.resident mcacr GROUP BY mcacr.id)')
            ->andWhere('r.id IN (SELECT ar.id FROM App:ContractAction aca JOIN aca.contract ac JOIN ac.resident ar WHERE aca.state='. ContractState::ACTIVE .' AND aca.end IS NULL)')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }
}