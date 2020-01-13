<?php

namespace App\Repository\Lead;

use App\Api\V1\Common\Service\Exception\Lead\IncorrectOwnerTypeException;
use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Facility;
use App\Entity\Lead\ActivityStatus;
use App\Entity\Lead\Activity;
use App\Entity\Lead\ActivityType;
use App\Entity\Lead\Contact;
use App\Entity\Lead\Lead;
use App\Entity\Lead\Organization;
use App\Entity\Lead\Outreach;
use App\Entity\Lead\OutreachType;
use App\Entity\Lead\Referral;
use App\Entity\Space;
use App\Entity\User;
use App\Model\Lead\ActivityOwnerType;
use App\Model\Lead\State;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ActivityRepository
 * @package App\Repository\Lead
 */
class ActivityRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     * @param null $ownerType
     * @param null $id
     * @param null $userId
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder, $ownerType = null, $id = null, $userId = null): void
    {
        $queryBuilder
            ->from(Activity::class, 'a')
            ->innerJoin(
                ActivityType::class,
                'at',
                Join::WITH,
                'at = a.type'
            )
            ->leftJoin(
                ActivityStatus::class,
                'st',
                Join::WITH,
                'st = a.status'
            )
            ->leftJoin(
                User::class,
                'u',
                Join::WITH,
                'u = a.assignTo'
            )
            ->leftJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = a.facility'
            )
            ->leftJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = a.lead'
            )
            ->leftJoin(
                Referral::class,
                'r',
                Join::WITH,
                'r = a.referral'
            )
            ->leftJoin(
                Organization::class,
                'ro',
                Join::WITH,
                'ro = r.organization'
            )
            ->leftJoin(
                Contact::class,
                'rc',
                Join::WITH,
                'rc = r.contact'
            )
            ->leftJoin(
                Organization::class,
                'o',
                Join::WITH,
                'o = a.organization'
            )
            ->leftJoin(
                Outreach::class,
                'ou',
                Join::WITH,
                'ou = a.outreach'
            )
            ->leftJoin(
                OutreachType::class,
                'oot',
                Join::WITH,
                'oot = ou.type'
            )
            ->leftJoin(
                Contact::class,
                'c',
                Join::WITH,
                'c = a.contact'
            )
            ->leftJoin(
                User::class,
                'cb',
                Join::WITH,
                'cb = a.createdBy'
            );

        if ($ownerType !== null && $id !== null) {
            switch ($ownerType) {
                case ActivityOwnerType::TYPE_LEAD:
                    $queryBuilder
                        ->where('l.id = :id')
                        ->setParameter('id', $id);

                    break;
                case ActivityOwnerType::TYPE_REFERRAL:
                    $queryBuilder
                        ->where('r.id = :id')
                        ->setParameter('id', $id);

                    break;
                case ActivityOwnerType::TYPE_ORGANIZATION:
                    $queryBuilder
                        ->where('o.id = :id')
                        ->setParameter('id', $id);

                    break;
                case ActivityOwnerType::TYPE_OUTREACH:
                    $queryBuilder
                        ->where('ou.id = :id')
                        ->setParameter('id', $id);

                    break;
                case ActivityOwnerType::TYPE_CONTACT:
                    $queryBuilder
                        ->where('c.id = :id')
                        ->setParameter('id', $id);

                    break;
                default:
                    throw new IncorrectOwnerTypeException();
            }
        }

        if ($userId !== null) {
            $queryBuilder
                ->andWhere('st.done = 0 AND u.id = :userId')
                ->setParameter('userId', $userId);
        }

        if ($space !== null) {
            $queryBuilder
                ->innerJoin(
                    ActivityStatus::class,
                    'ds',
                    Join::WITH,
                    'ds = at.defaultStatus'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ds.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->addOrderBy('a.date', 'DESC')
            ->groupBy('a.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null)
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->innerJoin(
                ActivityType::class,
                'at',
                Join::WITH,
                'at = a.type'
            );

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ActivityStatus::class,
                    'ds',
                    Join::WITH,
                    'ds = at.defaultStatus'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ds.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $qb
            ->addOrderBy('a.date', 'DESC');

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $ownerType
     * @param $id
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, $ownerType, $id)
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->innerJoin(
                ActivityType::class,
                'at',
                Join::WITH,
                'at = a.type'
            );

        switch ($ownerType) {
            case ActivityOwnerType::TYPE_LEAD:
                $qb
                    ->leftJoin(
                        Lead::class,
                        'l',
                        Join::WITH,
                        'l = a.lead'
                    )
                    ->where('l.id = :id')
                    ->setParameter('id', $id);

                break;
            case ActivityOwnerType::TYPE_REFERRAL:
                $qb
                    ->leftJoin(
                        Referral::class,
                        'r',
                        Join::WITH,
                        'r = a.referral'
                    )
                    ->where('r.id = :id')
                    ->setParameter('id', $id);

                break;
            case ActivityOwnerType::TYPE_ORGANIZATION:
                $qb
                    ->leftJoin(
                        Organization::class,
                        'o',
                        Join::WITH,
                        'o = a.organization'
                    )
                    ->where('o.id = :id')
                    ->setParameter('id', $id);

                break;
            case ActivityOwnerType::TYPE_OUTREACH:
                $qb
                    ->leftJoin(
                        Outreach::class,
                        'ou',
                        Join::WITH,
                        'ou = a.outreach'
                    )
                    ->where('ou.id = :id')
                    ->setParameter('id', $id);

                break;
            case ActivityOwnerType::TYPE_CONTACT:
                $qb
                    ->leftJoin(
                        Contact::class,
                        'c',
                        Join::WITH,
                        'c = a.contact'
                    )
                    ->where('c.id = :id')
                    ->setParameter('id', $id);

                break;
            default:
                throw new IncorrectOwnerTypeException();
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ActivityStatus::class,
                    'ds',
                    Join::WITH,
                    'ds = at.defaultStatus'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ds.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $userId
     * @return mixed
     */
    public function getMy(Space $space = null, array $entityGrants = null, $userId)
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->innerJoin(
                ActivityType::class,
                'at',
                Join::WITH,
                'at = a.type'
            )
            ->innerJoin(
                ActivityStatus::class,
                'st',
                Join::WITH,
                'st = a.status'
            )
            ->innerJoin(
                User::class,
                'u',
                Join::WITH,
                'u = a.assignTo'
            )
            ->where('st.done = 0 AND u.id = :userId')
            ->setParameter('userId', $userId);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ActivityStatus::class,
                    'ds',
                    Join::WITH,
                    'ds = at.defaultStatus'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ds.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->addOrderBy('a.date', 'DESC')
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
            ->createQueryBuilder('a')
            ->innerJoin(
                ActivityType::class,
                'at',
                Join::WITH,
                'at = a.type'
            )
            ->innerJoin(
                ActivityStatus::class,
                'ds',
                Join::WITH,
                'ds = at.defaultStatus'
            )
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ds.space'
            )
            ->where('a.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
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
            ->createQueryBuilder('a')
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ActivityType::class,
                    'at',
                    Join::WITH,
                    'at = a.type'
                )
                ->innerJoin(
                    ActivityStatus::class,
                    'ds',
                    Join::WITH,
                    'ds = at.defaultStatus'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ds.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('a.id')
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
            ->createQueryBuilder('a')
            ->select('a.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('a.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('a.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('a.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ActivityType::class,
                    'at',
                    Join::WITH,
                    'at = a.type'
                )
                ->innerJoin(
                    ActivityStatus::class,
                    'ds',
                    Join::WITH,
                    'ds = at.defaultStatus'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ds.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getActivityList(Space $space = null, array $entityGrants = null, $startDate, $endDate)
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->select(
                'a.id as id',
                'a.title as title',
                'ds.title as status',
                'f.name as facility',
                "(CASE
                    WHEN l.id IS NOT NULL THEN CONCAT('Lead : ', l.firstName, ' ', l.lastName)
                    WHEN r.id IS NOT NULL AND rc.id IS NOT NULL THEN CONCAT('Referral : ', rc.firstName, ' ', rc.lastName)
                    WHEN r.id IS NOT NULL AND rc.id IS NULL THEN CONCAT('Referral : ', ro.name)
                    WHEN o.id IS NOT NULL THEN CONCAT('Organization : ', o.name)
                    WHEN ou.id IS NOT NULL AND oot.id IS NOT NULL THEN CONCAT('Outreach : ', oot.title)
                    WHEN ou.id IS NOT NULL AND oot.id IS NULL THEN 'Outreach'
                    WHEN c.id IS NOT NULL THEN CONCAT('Contact : ', c.firstName, ' ', c.lastName)
                ELSE 'INVALID' END) as type",
                "CONCAT(u.firstName, ' ', u.lastName) as assignToFullName",
                "CONCAT(cb.firstName, ' ', cb.lastName) as enteredByFullName",
                'a.date as date',
                'a.dueDate as dueDate',
                'a.reminderDate as reminderDate',
                'a.notes as notes'
            )
            ->innerJoin(
                ActivityType::class,
                'at',
                Join::WITH,
                'at = a.type'
            )
            ->innerJoin(
                ActivityStatus::class,
                'ds',
                Join::WITH,
                'ds = at.defaultStatus'
            )
            ->leftJoin(
                User::class,
                'u',
                Join::WITH,
                'u = a.assignTo'
            )
            ->leftJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = a.facility'
            )
            ->leftJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = a.lead'
            )
            ->leftJoin(
                Referral::class,
                'r',
                Join::WITH,
                'r = a.referral'
            )
            ->leftJoin(
                Organization::class,
                'ro',
                Join::WITH,
                'ro = r.organization'
            )
            ->leftJoin(
                Contact::class,
                'rc',
                Join::WITH,
                'rc = r.contact'
            )
            ->leftJoin(
                Organization::class,
                'o',
                Join::WITH,
                'o = a.organization'
            )
            ->leftJoin(
                Outreach::class,
                'ou',
                Join::WITH,
                'ou = a.outreach'
            )
            ->leftJoin(
                OutreachType::class,
                'oot',
                Join::WITH,
                'oot = ou.type'
            )
            ->leftJoin(
                Contact::class,
                'c',
                Join::WITH,
                'c = a.contact'
            )
            ->leftJoin(
                User::class,
                'cb',
                Join::WITH,
                'cb = a.createdBy'
            )
            ->where('a.date >= :startDate')->setParameter('startDate', $startDate)
            ->andWhere('a.date < :endDate')->setParameter('endDate', $endDate);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ds.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->orderBy('a.date', 'DESC')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @return mixed
     */
    public function getActivitiesForCrontabNotification(Space $space = null, array $entityGrants = null)
    {
        $today = new \DateTime('now');
        $todayStart = $today->format('Y-m-d 00:00:00');
        $todayEnd = $today->format('Y-m-d 23:59:59');
        $qb = $this->createQueryBuilder('a')
            ->join('a.status', 'st')
            ->where('(a.dueDate IS NOT NULL AND a.dueDate <= :todayEnd AND st.done=0 ) OR (a.reminderDate >= :todayStart AND a.reminderDate <= :todayEnd)')
            ->setParameter('todayStart', $todayStart)
            ->setParameter('todayEnd', $todayEnd);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ActivityType::class,
                    'at',
                    Join::WITH,
                    'at = a.type'
                )
                ->innerJoin(
                    ActivityStatus::class,
                    'ds',
                    Join::WITH,
                    'ds = at.defaultStatus'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ds.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    ///////////// For Facility Dashboard ///////////////////////////////////////////////////////////////////////////////

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getLeadTourActivitiesForFacilityDashboard(Space $space = null, array $entityGrants = null, $startDate, $endDate)
    {
        $tour = 'tour';

        $qb = $this
            ->createQueryBuilder('a')
            ->select(
                'l.id as leadId',
                'f.id as typeId'
            )
            ->innerJoin(
                ActivityType::class,
                'at',
                Join::WITH,
                'at = a.type'
            )
            ->innerJoin(
                ActivityStatus::class,
                'st',
                Join::WITH,
                'st = a.status'
            )
            ->innerJoin(
                Lead::class,
                'l',
                Join::WITH,
                'l = a.lead'
            )
            ->innerJoin(
                Facility::class,
                'f',
                Join::WITH,
                'f = l.primaryFacility'
            )
            ->where('a.dueDate IS NOT NULL AND a.dueDate >= :startDate AND a.dueDate <= :endDate AND st.done = 1 AND l.state = :state')
            ->andWhere("at.title LIKE '%{$tour}%'")
            ->setParameter('state', State::TYPE_OPEN)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ActivityStatus::class,
                    'ds',
                    Join::WITH,
                    'ds = at.defaultStatus'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ds.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('a.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
    ///////////////// End For Facility Dashboard ///////////////////////////////////////////////////////////////////////
}
