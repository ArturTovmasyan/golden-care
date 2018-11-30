<?php

namespace App\Repository\Assessment;

use Doctrine\ORM\EntityRepository;

/**
 * Class FormCategoryRepository
 * @package App\Repository\Assessment
 */
class FormCategoryRepository extends EntityRepository
{
    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('afc');

        return $qb->where($qb->expr()->in('afc.id', $ids))
            ->groupBy('afc.id')
            ->getQuery()
            ->getResult();
    }
}