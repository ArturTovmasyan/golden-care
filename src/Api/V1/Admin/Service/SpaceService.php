<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Permission;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SpaceService
 * @package App\Api\V1\Service
 */
class SpaceService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Space::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Space::class)->findAll();
    }

    /**
     * @param $id
     * @return Space|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Space::class)->find($id);
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function edit($id, array $params): void
    {
        try {
            /**
             * @var Space $space
             * @var Permission $permission
             */
            $this->em->getConnection()->beginTransaction();

            $space = $this->em->getRepository(Space::class)->find($id);

            if (is_null($space)) {
                throw new SpaceNotFoundException();
            }

            $space->setName($params['name'] ?? null);
            $this->validate($space, null, ["api_admin_space_edit"]);

            $this->em->persist($space);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
