<?php

namespace App\Repository;

use App\Entity\Document;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class DocumentFileRepository
 * @package App\Repository
 */
class DocumentFileRepository extends EntityRepository
{
    /**
     * @param $id
     * @return mixed
     */
    public function getBy($id)
    {
        $qb = $this
            ->createQueryBuilder('df')
            ->innerJoin(
                Document::class,
                'd',
                Join::WITH,
                'd = df.document'
            )
            ->where('d.id = :id')
            ->setParameter('id', $id);

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
        $qb = $this
            ->createQueryBuilder('df')
            ->innerJoin(
                Document::class,
                'd',
                Join::WITH,
                'd = df.document'
            )
            ->where('d.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->getQuery()
            ->getResult();
    }
}