<?php

namespace App\Repository;

use App\Entity\ReportLog;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ReportLogRepository
 * @package App\Repository
 */
class ReportLogRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function search(QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->from(ReportLog::class, 'rl')
            ->innerJoin(
                User::class,
                'u',
                Join::WITH,
                'u = rl.createdBy'
            )
            ->orderBy('rl.createdAt', 'DESC')
            ->groupBy('rl.id');
    }
}