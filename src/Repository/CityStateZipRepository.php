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
     * @param Space|null $space
     * @param $id
     * @return mixed
     */
    public function getOne(Space $space = null, $id)
    {
        $qb = $this
            ->createQueryBuilder('csz')
            ->innerJoin(
                Space::class,
                's',
                Join::WITH,
                's = csz.space'
            )
            ->where('csz.id = :id')
            ->setParameter('id', $id);

        if ($space !== null) {
            $qb
                ->andWhere('s = :space')
                ->setParameter('space', $space);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
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