<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Space;
use App\Repository\SpaceRepository;
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
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var SpaceRepository $repo */
        $repo = $this->em->getRepository(Space::class);

        $repo->search($queryBuilder);
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
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $space = new Space();
            $space->setName($params['name'] ?? null);

            $this->validate($space, null, ['api_admin_space_add']);

            $this->em->persist($space);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $space->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Exception
     */
    public function edit($id, array $params): void
    {
        try {
            /**
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            $space = $this->em->getRepository(Space::class)->find($id);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $space->setName($params['name'] ?? null);

            $this->validate($space, null, ['api_admin_space_edit']);

            $this->em->persist($space);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Space $entity */
            $entity = $this->em->getRepository(Space::class)->find($id);

            if ($entity === null) {
                throw new SpaceNotFoundException();
            }

            $this->em->remove($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $ids
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new SpaceNotFoundException();
            }

            /** @var SpaceRepository $repo */
            $repo = $this->em->getRepository(Space::class);

            $spaces = $repo->findByIds($ids);

            if (empty($spaces)) {
                throw new SpaceNotFoundException();
            }

            /**
             * @var Space $space
             */
            foreach ($spaces as $space) {
                $this->em->remove($space);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
