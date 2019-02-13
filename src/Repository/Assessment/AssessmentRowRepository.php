<?php

namespace App\Repository\Assessment;

use App\Entity\Assessment\Assessment;
use App\Entity\Resident;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class AssessmentRowRepository
 * @package App\Repository\Assessment
 */
class AssessmentRowRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param Assessment $assessment
     * @return mixed
     */
    public function getBy(Space $space = null, $assessment)
    {
        $qb = $this
            ->createQueryBuilder('ar')
            ->innerJoin(
                Assessment::class,
                'a',
                Join::WITH,
                'a = ar.assessment'
            )
            ->where('a = :assessment')
            ->setParameter('assessment', $assessment);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Resident::class,
                    'r',
                    Join::WITH,
                    'r = a.resident'
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

        return $qb
            ->getQuery()
            ->getResult();
    }
}