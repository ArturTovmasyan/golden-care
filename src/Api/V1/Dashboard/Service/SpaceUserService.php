<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SpaceUserService
 * @package App\Api\V1\Dashboard\Service
 */
class SpaceUserService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(User::class)->findUsersBySpace($queryBuilder, $params[0]);
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
        return $this->em->getRepository(User::class)->findUserBySpaceAndId($space, $id);
    }
}
