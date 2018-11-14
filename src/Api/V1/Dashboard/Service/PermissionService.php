<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Permission;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class PermissionService
 * @package App\Api\V1\Service
 */
class PermissionService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Permission::class)->searchForDashboard($queryBuilder, $params[0]);
    }

    public function list($params)
    {
        return null;
    }
}