<?php

namespace App\Repository;

use App\Entity\CityStateZip;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CityStateZipRepository
 * @package App\Repository
 */
class CityStateZipRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(CityStateZip::class, 'csz')
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = csz.space'
            )
            ->groupBy('csz.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('csz');

        return $qb->where($qb->expr()->in('csz.id', $ids))
            ->groupBy('csz.id')
            ->getQuery()
            ->getResult();
    }
}