<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Role;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class SpaceRoleService
 * @package App\Api\V1\Dashboard\Service
 */
class SpaceRoleService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Role::class)->findBySpace($queryBuilder, $params[0]);
    }

    public function list($params)
    {
        return null;
    }

    /**
     * @param Space $space
     * @param $id
     * @return mixed
     */
    public function getBySpaceAndId(Space $space, $id)
    {
        return $this->em->getRepository(Role::class)->findBySpaceAndId($space, $id);
    }
}