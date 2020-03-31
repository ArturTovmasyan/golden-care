<?php

namespace App\Repository;

use App\Entity\EmailLog;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class EmailLogRepository
 * @package App\Repository
 */
class EmailLogRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function search(QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->from(EmailLog::class, 'el')
            ->orderBy('el.createdAt', 'DESC')
            ->groupBy('el.id');
    }
}