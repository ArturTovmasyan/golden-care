<?php

namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\HobbyNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\Hobby;
use App\Entity\Space;
use App\Repository\Lead\HobbyRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class HobbyService
 * @package App\Api\V1\Admin\Service
 */
class HobbyService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var HobbyRepository $repo */
        $repo = $this->em->getRepository(Hobby::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Hobby::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var HobbyRepository $repo */
        $repo = $this->em->getRepository(Hobby::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Hobby::class));
    }

    /**
     * @param $id
     * @return Hobby|null|object
     */
    public function getById($id)
    {
        /** @var HobbyRepository $repo */
        $repo = $this->em->getRepository(Hobby::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Hobby::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $hobby = new Hobby();
            $hobby->setTitle($params['title']);
            $hobby->setSpace($space);

            $this->validate($hobby, null, ['api_lead_hobby_add']);

            $this->em->persist($hobby);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $hobby->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Throwable
     */
    public function edit($id, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            /** @var HobbyRepository $repo */
            $repo = $this->em->getRepository(Hobby::class);

            /** @var Hobby $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Hobby::class), $id);

            if ($entity === null) {
                throw new HobbyNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_lead_hobby_edit']);

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

            /** @var HobbyRepository $repo */
            $repo = $this->em->getRepository(Hobby::class);

            /** @var Hobby $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Hobby::class), $id);

            if ($entity === null) {
                throw new HobbyNotFoundException();
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
                throw new HobbyNotFoundException();
            }

            /** @var HobbyRepository $repo */
            $repo = $this->em->getRepository(Hobby::class);

            $hobbies = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Hobby::class), $ids);

            if (empty($hobbies)) {
                throw new HobbyNotFoundException();
            }

            /**
             * @var Hobby $hobby
             */
            foreach ($hobbies as $hobby) {
                $this->em->remove($hobby);
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
            throw new HobbyNotFoundException();
        }

        /** @var HobbyRepository $repo */
        $repo = $this->em->getRepository(Hobby::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Hobby::class), $ids);

        if (empty($entities)) {
            throw new HobbyNotFoundException();
        }

        return $this->getRelatedData(Hobby::class, $entities);
    }
}
