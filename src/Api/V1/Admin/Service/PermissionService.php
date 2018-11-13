<?php
namespace App\Api\V1\Admin\Service;

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
     * @param mixed $params
     * @return Paginator
     */
    public function getListing(QueryBuilder $queryBuilder, $params)
    {
        return $this->em->getRepository(Permission::class)->searchAllPermissions($queryBuilder);
    }
}