<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Role;
use App\Entity\Space;

/**
 * Class SpaceRoleService
 * @package App\Api\V1\Dashboard\Service
 */
class SpaceRoleService extends BaseService
{
    /**
     * @param Space $space
     * @return Role[]|array
     */
    public function getListingBySpace(Space $space)
    {
        return $this->em->getRepository(Role::class)->findRolesBySpace($space);
    }
}