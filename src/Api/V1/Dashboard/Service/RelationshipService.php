<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Relationship;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;

/**
 * Class RoleService
 * @package App\Api\V1\Dashboard\Service
 */
class RelationshipService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Relationship::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Relationship::class)->findAll();
    }
}