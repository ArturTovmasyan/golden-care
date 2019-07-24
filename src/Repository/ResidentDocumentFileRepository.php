<?php

namespace App\Repository;

use App\Entity\ResidentDocument;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class ResidentDocumentFileRepository
 * @package App\Repository
 */
class ResidentDocumentFileRepository extends EntityRepository
{
    /**
     * @param $id
     * @return mixed
     */
    public function getBy($id)
    {
        $qb = $this
            ->createQueryBuilder('rdf')
            ->innerJoin(
                ResidentDocument::class,
                'rd',
                Join::WITH,
                'rd = rdf.residentDocument'
            )
            ->where('rd.id = :id')
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
            ->createQueryBuilder('rdf')
            ->innerJoin(
                ResidentDocument::class,
                'rd',
                Join::WITH,
                'rd = rdf.residentDocument'
            )
            ->where('rd.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->getQuery()
            ->getResult();
    }
}