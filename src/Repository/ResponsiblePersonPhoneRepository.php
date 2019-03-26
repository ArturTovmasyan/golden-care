<?php

namespace App\Repository;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\ResponsiblePerson;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class ResponsiblePersonPhoneRepository
 * @package App\Repository
 */
class ResponsiblePersonPhoneRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param ResponsiblePerson $responsiblePerson
     * @return mixed
     */
    public function getBy(Space $space = null, array $entityGrants = null, ResponsiblePerson $responsiblePerson)
    {
        $qb = $this
            ->createQueryBuilder('rrp')
            ->innerJoin(
                ResponsiblePerson::class,
                'rp',
                Join::WITH,
                'rp = rrp.responsiblePerson'
            )
            ->where('rp = :responsiblePerson')
            ->setParameter('responsiblePerson', $responsiblePerson);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rp.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rrp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array $responsiblePersonIds
     * @return mixed
     */
    public function getByResponsiblePersonIds(Space $space = null, array $entityGrants = null, array $responsiblePersonIds)
    {
        $qb = $this->createQueryBuilder('rpp');

        $qb
            ->select('
                    rpp.id as id,
                    rp.id as rpId,
                    rpp.primary as primary,
                    rpp.type as type,
                    rpp.extension as extension,
                    rpp.number as number
            ')
            ->innerJoin(
                ResponsiblePerson::class,
                'rp',
                Join::WITH,
                'rpp.responsiblePerson = rp'
            )
            ->where($qb->expr()->in('rp.id', $responsiblePersonIds));

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rp.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rrp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('rpp.id')
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
            ->createQueryBuilder('rpp')
            ->select('rpp.number');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('rpp.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('rpp.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('rpp.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    ResponsiblePerson::class,
                    'rp',
                    Join::WITH,
                    'rpp.responsiblePerson = rp'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = rp.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('rpp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
