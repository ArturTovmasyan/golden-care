<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class FileRepository
 * @package App\Repository
 */
class FileRepository extends EntityRepository
{
    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this
            ->createQueryBuilder('f')
            ->where('f.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->getQuery()
            ->getResult();
    }
}