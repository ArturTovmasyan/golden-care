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
use App\Entity\Space;
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
     * @param Space|null $space
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResidentPhysician::class, 'rp')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rp.resident'
            )
            ->innerJoin(
                Physician::class,
                'p',
                Join::WITH,
                'p = rp.physician'
            )
            ->innerJoin(
                Salutation::class,
                'ps',
                Join::WITH,
                'ps = p.salutation'
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
            ->groupBy('rp.id');
    }

    /**
     * @param Space|null $space
     * @param $id
     * @return mixed
     */
    public function getBy(Space $space = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('rp')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rp.resident'
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
            ->createQueryBuilder('rp')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rp.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('rp.id = :id')
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
     * @param $id
     * @return mixed
     */
    public function getOnePrimaryByResidentId(Space $space = null, $resident_id)
    {
        $qb = $this
            ->createQueryBuilder('rp')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rp.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('r.id = :resident_id')
            ->andWhere('rp.primary = :primary')
            ->setParameter('resident_id', $resident_id)
            ->setParameter('primary', true);

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
     * @param $id
     * @return mixed
     */
    public function getPrimariesByResidentId(Space $space = null, $resident_id)
    {
        $qb = $this
            ->createQueryBuilder('rp')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rp.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('r.id = :resident_id')
            ->andWhere('rp.primary = :primary')
            ->setParameter('resident_id', $resident_id)
            ->setParameter('primary', true);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param Resident $resident
     * @return mixed
     */
    public function getOneBy(Space $space = null, Resident $resident)
    {
        $qb = $this
            ->createQueryBuilder('rp')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rp.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('r = :resident AND rp.primary = 1')
            ->setParameter('resident', $resident);

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
        $qb = $this->createQueryBuilder('rp');

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = rp.resident'
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

        return $qb->where($qb->expr()->in('rp.id', $ids))
            ->groupBy('rp.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param $type
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(Space $space = null, $type, array $residentIds)
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
            ->groupBy('rp.id')
            ->getQuery()
            ->getResult();
    }
}
