<?php

namespace App\Repository\Assessment;

use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Assessment\Category;
use App\Entity\Assessment\Form;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class FormCategoryRepository
 * @package App\Repository\Assessment
 */
class FormCategoryRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param $ids
     * @return mixed
     */
    public function findByIds(Space $space = null, $ids)
    {
        $qb = $this
            ->createQueryBuilder('afc')
            ->where('afc.id IN (:ids)')
            ->setParameter('ids', $ids);

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

        return $qb->groupBy('afc.id')
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
            ->createQueryBuilder('afc')
            ->innerJoin(
                Category::class,
                'c',
                Join::WITH,
                'c = afc.category'
            )
            ->select('c.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('afc.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('afc.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('afc.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
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

        if ($entityGrants !== null) {
            $qb
                ->andWhere('afc.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}