<?php

namespace App\Repository;

use App\Entity\ResponsiblePerson;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class ResponsiblePersonPhoneRepository
 * @package App\Repository
 */
class ResponsiblePersonPhoneRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param ResponsiblePerson $responsiblePerson
     * @return mixed
     */
    public function getBy(Space $space = null, ResponsiblePerson $responsiblePerson)
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

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array $responsiblePersonIds
     * @return mixed
     */
    public function getByResponsiblePersonIds(Space $space = null, array $responsiblePersonIds)
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

        return $qb
            ->groupBy('rpp.id')
            ->getQuery()
            ->getResult();
    }
}
