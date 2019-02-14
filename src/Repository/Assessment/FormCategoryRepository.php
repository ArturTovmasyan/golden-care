<?php

namespace App\Repository\Assessment;

use App\Entity\Assessment\Category;
use App\Entity\Assessment\Form;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class FormCategoryRepository
 * @package App\Repository\Assessment
 */
class FormCategoryRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @param $ids
     * @return mixed
     */
    public function findByIds(Space $space = null, $ids)
    {
        $qb = $this->createQueryBuilder('afc');

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Category::class,
                    'c',
                    Join::WITH,
                    'c = afc.category'
                )
                ->innerJoin(
                    Form::class,
                    'f',
                    Join::WITH,
                    'f = afc.form'
                )
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = f.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb->where($qb->expr()->in('afc.id', $ids))
            ->groupBy('afc.id')
            ->getQuery()
            ->getResult();
    }
}