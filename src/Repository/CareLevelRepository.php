<?php

namespace App\Repository;

use App\Entity\CareLevel;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CareLevelRepository
 * @package App\Repository
 */
class CareLevelRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(CareLevel::class, 'cl')
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = cl.space'
            )
            ->groupBy('cl.id');
    }
}