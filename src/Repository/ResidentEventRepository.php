<?php

namespace App\Repository;

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
class ResidentEventRepository extends EntityRepository
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
                ->andWhere('re.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
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
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIds(Space $space = null, array $residentIds)
    {
        $qb = $this->createQueryBuilder('re');

        $qb
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
            ->where($qb->expr()->in('r.id', $residentIds));

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
            ->orderBy('re.date', 'DESC')
            ->groupBy('re.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param $startDate
     * @param $endDate
     * @param array $residentIds
     * @return mixed
     */
    public function getByResidentIdsAndDate(Space $space = null, $startDate, $endDate, array $residentIds)
    {
        $qb = $this->createQueryBuilder('re');

        $qb
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
            ->andWhere('re.date>=:startDate')
            ->andWhere('re.date<=:endDate')
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

        return $qb
            ->orderBy('re.date', 'DESC')
            ->groupBy('re.id')
            ->getQuery()
            ->getResult();
    }
}