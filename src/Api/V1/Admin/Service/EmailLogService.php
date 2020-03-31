<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\EmailLog;
use App\Repository\EmailLogRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class EmailLogService
 * @package App\Api\V1\Admin\Service
 */
class EmailLogService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var EmailLogRepository $repo */
        $repo = $this->em->getRepository(EmailLog::class);

        $repo->search($queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        return $this->em->getRepository(EmailLog::class)->findAll();
    }
}
