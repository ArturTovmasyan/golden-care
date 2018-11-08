<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class SpaceUserService
 * @package App\Api\V1\Dashboard\Service
 */
class SpaceUserService extends BaseService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param Space $space
     * @return Paginator
     */
    public function getListingBySpace(QueryBuilder $queryBuilder, Space $space)
    {
        return $this->em->getRepository(User::class)->findUsersBySpace($queryBuilder, $space);
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