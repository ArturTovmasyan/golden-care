<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ReportLog;
use App\Repository\ReportLogRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ReportLogService
 * @package App\Api\V1\Admin\Service
 */
class ReportLogService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var ReportLogRepository $repo */
        $repo = $this->em->getRepository(ReportLog::class);

        $repo->search($queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        return $this->em->getRepository(ReportLog::class)->findAll();
    }
}
