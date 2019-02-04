<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Entity\CityStateZip;
use App\Entity\Contract;
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
use App\Model\ContractType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentPhysicianRepository
 * @package App\Repository
 */
class ResidentPhysicianRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param bool $residentId
     */
    public function search(QueryBuilder $queryBuilder, $residentId = false)
    {
        $queryBuilder
            ->from(ResidentPhysician::class, 'rp')
            ->leftJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rp.resident'
            )
            ->leftJoin(
                Physician::class,
                'p',
                Join::WITH,
                'p = rp.physician'
            )
            ->leftJoin(
                Salutation::class,
                'ps',
                Join::WITH,
                'ps = p.salutation'
            )
            ->groupBy('rp.id');

        if ($residentId) {
            $queryBuilder
                ->where('rp.resident = :residentId')
                ->setParameter('residentId', $residentId);
        }
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('rp');

        return $qb->where($qb->expr()->in('rp.id', $ids))
            ->groupBy('rp.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $type
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds($type, array $residentIds)
    {
        $qb = $this->createQueryBuilder('rp');

        $qb
            ->select('
                    rp.id as id,
                    r.id as residentId,
                    rp.primary as primary,
                    p.firstName as firstName,
                    p.lastName as lastName,
                    p.address_1 as address,
                    p.officePhone as officePhone,
                    p.emergencyPhone as emergencyPhone,
                    p.email as email,
                    p.fax as fax,
                    p.websiteUrl as websiteUrl,
                    csz.stateFull as state,
                    csz.zipMain as zip,
                    csz.city as city,
                    sal.title as salutation,
                    p.id as pId
            ')
            ->innerJoin(
                Physician::class,
                'p',
                Join::WITH,
                'rp.physician = p'
            )
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'rp.resident = r'
            )
            ->innerJoin(
                Contract::class,
                'c',
                Join::WITH,
                'c.resident = r'
            )
            ->innerJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'p.salutation = sal'
            )
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'p.csz = csz'
            );

            switch ($type) {
                case ContractType::TYPE_FACILITY:
                    $qb
                        ->addSelect(
                            'f.id as typeId,
                        f.name as typeName,
                        f.shorthand as typeShorthand,
                        fr.number as roomNumber,
                        fb.number as bedNumber'
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
                        );

                    $qb
                        ->orderBy('f.name')
                        ->addOrderBy('rp.primary', 'DESC')
                        ->addOrderBy('p.lastName');

                    break;
                case ContractType::TYPE_REGION:
                    $qb
                        ->addSelect(
                            'reg.id as typeId,
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
                        );

                    $qb
                        ->orderBy('reg.name')
                        ->addOrderBy('rp.primary', 'DESC')
                        ->addOrderBy('p.lastName');

                    break;
                default:
                    throw new IncorrectStrategyTypeException();
            }

        return $qb
            ->where($qb->expr()->in('r.id', $residentIds))
            ->groupBy('rp.id')
            ->getQuery()
            ->getResult();
    }
}
