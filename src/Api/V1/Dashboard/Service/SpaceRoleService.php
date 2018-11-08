<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Role;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class SpaceRoleService
 * @package App\Api\V1\Dashboard\Service
 */
class SpaceRoleService extends BaseService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param Space $space
     * @return mixed
     */
    public function getListingBySpace(QueryBuilder $queryBuilder, Space $space)
    {
        return $this->em->getRepository(Role::class)->findRolesBySpace($queryBuilder, $space);
    }

    /**
     * @param Space $space
     * @param $id
     * @return mixed
     */
    public function getBySpaceAndId(Space $space, $id)
    {
        return $this->em->getRepository(Role::class)->findRolesBySpaceAndId($space, $id);
    }
}