<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\User;

/**
 * Class SpaceUserService
 * @package App\Api\V1\Dashboard\Service
 */
class SpaceUserService extends BaseService
{
    /**
     * @param Space $space
     * @return User[]|array
     */
    public function getListingBySpace(Space $space)
    {
        return $this->em->getRepository(User::class)->findUsersBySpace($space);
    }

    /**
     * @param Space $space
     * @return int
     */
    public function getTotalListingBySpace(Space $space)
    {
        return $this->em->getRepository(User::class)->findTotalUsersBySpace($space);
    }

    /**
     * @param Space $space
     * @return User|null
     */
    public function getBySpaceAndId(Space $space, $id)
    {
        return $this->em->getRepository(User::class)->findUserBySpaceAndId($space, $id);
    }
}