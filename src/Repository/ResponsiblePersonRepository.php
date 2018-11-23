<?php

namespace App\Repository;

use App\Entity\CityStateZip;
use App\Entity\ResponsiblePerson;
use App\Entity\Salutation;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResponsiblePersonRepository
 * @package App\Repository
 */
class ResponsiblePersonRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(ResponsiblePerson::class, 'rp')
            ->leftJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'sal = rp.salutation'
            )
            ->leftJoin(
                CityStateZip::class,
                'cs',
                Join::WITH,
                'cs = rp.csz'
            )
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = rp.space'
            )
            ->groupBy('rp.id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('rp');

        return $qb->where($qb->expr()->in('rp.id', $ids))
            ->groupBy('rp.id')
            ->getQuery()
            ->getResult();
    }
}
