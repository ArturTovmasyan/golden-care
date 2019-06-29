<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\EventDefinition;
use App\Entity\Physician;
use App\Entity\Resident;
use App\Entity\ResidentEvent;
use App\Entity\ResponsiblePerson;
use App\Entity\Salutation;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentEventRepository
 * @package App\Repository
 */
class ResidentEventRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(ResidentEvent::class, 're')

            ->addSelect("
                JSON_ARRAY(
                    JSON_OBJECT('Date Added', re.additionalDate),
                    JSON_OBJECT('Physician', CONCAT(COALESCE(ps.title,''), ' ', COALESCE(p.firstName, ''), ' ', COALESCE(p.middleName, ''), ' ', COALESCE(p.lastName, ''))),
                    
                    JSON_OBJECT('Responsible Person(s)', JSON_ARRAYAGG(
                            CONCAT(
                                COALESCE(rpss.title,''),
                                ' ',
                                COALESCE(rps.firstName, ''),
                                ' ',
                                COALESCE(rps.middleName, ''),
                                ' ',
                                COALESCE(rps.lastName, '')
                            )
                        )
                    )
                ) as info
            ")

            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = re.resident'
            )
            ->innerJoin(
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
                're.responsiblePersons',
                'rps'
            )
            ->leftJoin(
                Salutation::class,
                'rpss',
                Join::WITH,
                'rpss = rps.salutation'
            )
        ;

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
                ->andWhere('re.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->orderBy('re.data', 'DESC')
            ->groupBy('re.id');
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
            ->createQueryBuilder('re')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = re.resident'
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
                ->andWhere('re.id IN (:grantIds)')
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
            ->createQueryBuilder('re')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = re.resident'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = r.space'
            )
            ->where('re.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('re.id IN (:grantIds)')
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
            ->createQueryBuilder('re')
            ->where('re.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = re.resident'
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
                ->andWhere('re.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('re.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(Space $space = null, array $entityGrants = null, array $residentIds)
    {
        $qb = $this->createQueryBuilder('re');

        $qb
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
            ->leftJoin('re.responsiblePersons','rps')
            ->leftJoin(
                Salutation::class,
                'rpsal',
                Join::WITH,
                'rps.salutation = rpsal'
            )
            ->where('r.id IN (:residentIds)')
            ->setParameter('residentIds', $residentIds);

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
                ->andWhere('re.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('re.date', 'DESC')
            ->groupBy('re.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $startDate
     * @param $endDate
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIdsAndDate(Space $space = null, array $entityGrants = null, $startDate, $endDate, array $residentIds)
    {
        $qb = $this->createQueryBuilder('re');

        $qb
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
            ->leftJoin('re.responsiblePersons','rps')
            ->leftJoin(
                Salutation::class,
                'rpsal',
                Join::WITH,
                'rps.salutation = rpsal'
            )
            ->where('r.id IN (:residentIds)')
            ->andWhere('re.date>=:startDate')
            ->andWhere('re.date<=:endDate')
            ->setParameter('residentIds', $residentIds)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

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
                ->andWhere('re.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('re.date', 'DESC')
            ->groupBy('re.id')
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
            ->createQueryBuilder('re')
            ->innerJoin(
                EventDefinition::class,
                'ed',
                Join::WITH,
                're.definition = ed'
            )
            ->select('ed.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('re.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('re.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('re.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = re.resident'
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
                ->andWhere('re.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}