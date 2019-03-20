<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CareLevelNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CareLevel;
use App\Entity\Space;
use App\Repository\CareLevelRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CareLevelService
 * @package App\Api\V1\Admin\Service
 */
class CareLevelService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var CareLevelRepository $repo */
        $repo = $this->em->getRepository(CareLevel::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var CareLevelRepository $repo */
        $repo = $this->em->getRepository(CareLevel::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevel::class));
    }

    /**
     * @param $id
     * @return CareLevel|null|object
     */
    public function getById($id)
    {
        /** @var CareLevelRepository $repo */
        $repo = $this->em->getRepository(CareLevel::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Exception
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $careLevel = new CareLevel();
            $careLevel->setTitle($params['title']);
            $careLevel->setDescription($params['description']);
            $careLevel->setSpace($space);

            $this->validate($careLevel, null, ['api_admin_care_level_add']);

            $this->em->persist($careLevel);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $careLevel->getId();
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
    public function edit($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            /** @var CareLevelRepository $repo */
            $repo = $this->em->getRepository(CareLevel::class);

            /** @var CareLevel $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $id);

            if ($entity === null) {
                throw new CareLevelNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setDescription($params['description']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_care_level_edit']);

            $this->em->persist($entity);
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

            /** @var CareLevelRepository $repo */
            $repo = $this->em->getRepository(CareLevel::class);

            /** @var CareLevel $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $id);

            if ($entity === null) {
                throw new CareLevelNotFoundException();
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
    public function removeBulk(array $ids)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new CareLevelNotFoundException();
            }

            /** @var CareLevelRepository $repo */
            $repo = $this->em->getRepository(CareLevel::class);

            $careLevels = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $ids);

            if (empty($careLevels)) {
                throw new CareLevelNotFoundException();
            }

            /**
             * @var CareLevel $careLevel
             */
            foreach ($careLevels as $careLevel) {
                $this->em->remove($careLevel);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new CareLevelNotFoundException();
        }

        /** @var CareLevelRepository $repo */
        $repo = $this->em->getRepository(CareLevel::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $ids);

        if (empty($entities)) {
            throw new CareLevelNotFoundException();
        }

        return $this->getRelatedData(CareLevel::class, $entities);
    }
}
