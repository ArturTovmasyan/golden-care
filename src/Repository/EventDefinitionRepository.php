<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\EventDefinition;
use App\Entity\Space;
use App\Model\GroupType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class EventDefinitionRepository
 * @package App\Repository
 */
class EventDefinitionRepository extends EntityRepository implements RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param QueryBuilder $queryBuilder
     */
    public function search(Space $space = null, array $entityGrants = null, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->from(EventDefinition::class, 'ed')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ed.space'
            );

        if ($space !== null) {
            $queryBuilder
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $queryBuilder
                ->andWhere('ed.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $queryBuilder
            ->groupBy('ed.id');
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param null $view
     * @param null $type
     * @return mixed
     */
    public function list(Space $space = null, array $entityGrants = null, $view = null, $type = null)
    {
        $qb = $this
            ->createQueryBuilder('ed')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ed.space'
            );

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ed.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        $qb
            ->andWhere('ed.inChooser=:inChooser')
            ->setParameter('inChooser', true);

        if ($view !== null) {
            $qb
                ->andWhere('ed.view=:view')
                ->setParameter('view', $view);
        }

        if ($type !== null) {
            switch ($type) {
                case GroupType::TYPE_FACILITY:
                    $qb
                        ->andWhere('ed.ffc = 1');
                    break;
                case GroupType::TYPE_APARTMENT:
                    $qb
                        ->andWhere('ed.il = 1');
                    break;
                case GroupType::TYPE_REGION:
                    $qb
                        ->andWhere('ed.ihc = 1');
                    break;
                default:
                    throw new IncorrectStrategyTypeException();
            }
        }

        $qb
            ->addOrderBy('ed.title', 'ASC');

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
            ->createQueryBuilder('ed')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ed.space'
            )
            ->where('ed.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ed.id IN (:grantIds)')
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
            ->createQueryBuilder('ed')
            ->where('ed.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ed.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ed.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb->groupBy('ed.id')
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
            ->createQueryBuilder('ed')
            ->select('ed.title');

        if ($mappedBy !== null && $id !== null) {
            $qb
                ->where('ed.' . $mappedBy . '= :id')
                ->setParameter('id', $id);
        }

        if ($ids !== null) {
            $qb
                ->andWhere('ed.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if ($mappedBy === null && $id === null && $ids === null) {
            $qb
                ->andWhere('ed.id IN (:array)')
                ->setParameter('array', []);
        }

        if ($space !== null) {
            $qb
                ->innerJoin(
                    Space::class,
                    's',
                    Join::WITH,
                    's = ed.space'
                )
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ed.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param $type
     * @return mixed
     */
    public function getOneByType(Space $space = null, array $entityGrants = null, $type)
    {
        $qb = $this
            ->createQueryBuilder('ed')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ed.space'
            )
            ->where('ed.type = :type')
            ->setParameter('type', $type);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        if ($entityGrants !== null) {
            $qb
                ->andWhere('ed.id IN (:grantIds)')
                ->setParameter('grantIds', $entityGrants);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Space|null $space
     * @param $title
     * @return mixed
     */
    public function getByTitle(Space $space = null, $title)
    {
        $qb = $this
            ->createQueryBuilder('ed')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = ed.space'
            )
            ->andWhere("ed.title LIKE '%{$title}%'");

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}