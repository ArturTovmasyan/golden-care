<?php

namespace App\Repository\Lead;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Lead\Contact;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class ContactPhoneRepository
 * @package App\Repository
 */
class ContactPhoneRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param Contact $contact
     * @return mixed
     *
     */
    public function getBy(Space $space = null, array $entityGrants = null, Contact $contact)
    {
        $qb = $this
            ->createQueryBuilder('cp')
            ->innerJoin(
                Contact::class,
                'c',
                Join::WITH,
                'c = cp.contact'
            )
            ->where('c = :contact')
            ->setParameter('contact', $contact);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = c.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('cp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param array $contactIds
     * @return mixed
     */
    public function getByContactIds(Space $space = null, array $entityGrants = null, array $contactIds)
    {
        $qb = $this->createQueryBuilder('cp');

        $qb
            ->select('
                    cp.id as id,
                    c.id as cId,
                    cp.primary as primary,
                    cp.type as type,
                    cp.number as number
            ')
            ->innerJoin(
                Contact::class,
                'c',
                Join::WITH,
                'c = cp.contact'
            )
            ->where('c.id IN (:contactIds)')
            ->setParameter('contactIds', $contactIds);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = c.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('cp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->groupBy('cp.id')
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
            ->createQueryBuilder('cp')
            ->select('cp.number');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('cp.'.$mappedBy.'= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('cp.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('cp.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Contact::class,
                    'c',
                    Join::WITH,
                    'c = cp.contact'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = c.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('cp.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
