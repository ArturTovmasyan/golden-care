<?php

namespace App\Repository;

use App\Entity\Resident;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class ResidentPhoneRepository
 * @package App\Repository
 */
class ResidentPhoneRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param Resident $resident
     * @return mixed
     */
    public function getBy(Space $space = null, Resident $resident)
    {
        $qb = $this
            ->createQueryBuilder('rp')
            ->innerJoin(
                Resident::class,
                'r',
                Join::WITH,
                'r = rp.resident'
            )
            ->where('r = :resident')
            ->setParameter('resident', $resident);

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
}
