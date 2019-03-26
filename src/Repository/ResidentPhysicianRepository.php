<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\CityStateZip;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Physician;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentAdmission;
use App\Entity\ResidentPhysician;
use App\Entity\Salutation;
use App\Entity\Space;
use App\Model\GroupType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentPhysicianRepository
 * @package App\Repository
 */
class ResidentPhysicianRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
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

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('rp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('rp.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, $id)
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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $id
     * @return mixed
     */
    public function getOne(Space $space = null, array $entityGrants = null, $id)
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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $resident_id
     * @return mixed
     */
    public function getOnePrimaryByResidentId(Space $space = null, array $entityGrants = null, $resident_id)
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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $resident_id
     * @return mixed
     */
    public function getPrimariesByResidentId(Space $space = null, array $entityGrants = null, $resident_id)
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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param Resident $resident
     * @return mixed
     */
    public function getOneBy(Space $space = null, array $entityGrants = null, Resident $resident)
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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
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
            ->createQueryBuilder('rp')
            ->where('rp.id IN (:ids)')
            ->setParameter('ids', $ids);

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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('rp.id')
            ->getQuery()
            ->getResult();
    }

    //////////////////////////// Admission Part///////////////////////////////////////////////////
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @param array $residentIds
     * @return mixed
     */
    public function getByAdmissionResidentIds(Space $space = null, array $entityGrants = null, $type, array $residentIds)
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
                ResidentAdmission::class,
                'ra',
                Join::WITH,
                'ra.resident = r'
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
            )
            ->where($qb->expr()->in('r.id', $residentIds));

        switch ($type) {
            case GroupType::TYPE_FACILITY:
                $qb
                    ->addSelect(
                        'f.id as typeId,
                        f.name as typeName,
                        f.shorthand as typeShorthand,
                        fr.number as roomNumber,
                        fb.number as bedNumber'
                    )
                    ->innerJoin(
                        FacilityBed::class,
                        'fb',
                        Join::WITH,
                        'ra.facilityBed = fb'
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
            case GroupType::TYPE_REGION:
                $qb
                    ->addSelect(
                        'reg.id as typeId,
                        reg.shorthand as typeShorthand,
                        reg.name as typeName'
                    )
                    ->innerJoin(
                        Region::class,
                        'reg',
                        Join::WITH,
                        'ra.region = reg'
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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('rp.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param null $mappedBy
     * @param null $id
     * @param array|null $ids
     * @return mixed
     */
    public function getRelatedData(Space $space = null, array $entityGrants = null, $mappedBy = null, $id = null, array $ids = null)
    {
        $qb = $this
            ->createQueryBuilder('rp')
            ->innerJoin(
                Physician::class,
                'p',
                Join::WITH,
                'rp.physician = p'
            )
            ->select("CONCAT(p.firstName, ' ', p.lastName) as fullName");

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('rp.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('rp.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
