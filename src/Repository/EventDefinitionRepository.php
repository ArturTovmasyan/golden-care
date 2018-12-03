<?php

namespace App\Repository;

use App\Entity\EventDefinition;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class EventDefinitionRepository
 * @package App\Repository
 */
class EventDefinitionRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(EventDefinition::class, 'ed')
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = ed.space'
            )
            ->groupBy('ed.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('ed');

        return $qb->where($qb->expr()->in('ed.id', $ids))
            ->groupBy('ed.id')
            ->getQuery()
            ->getResult();
    }
}