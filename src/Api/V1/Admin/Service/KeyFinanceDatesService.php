<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\KeyFinanceDatesNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\KeyFinanceDates;
use App\Entity\Space;
use App\Repository\KeyFinanceDatesRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class KeyFinanceDatesService
 * @package App\Api\V1\Admin\Service
 */
class KeyFinanceDatesService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var KeyFinanceDatesRepository $repo */
        $repo = $this->em->getRepository(KeyFinanceDates::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(KeyFinanceDates::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var KeyFinanceDatesRepository $repo */
        $repo = $this->em->getRepository(KeyFinanceDates::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(KeyFinanceDates::class));
    }

    /**
     * @param $id
     * @return KeyFinanceDates|null|object
     */
    public function getById($id)
    {
        /** @var KeyFinanceDatesRepository $repo */
        $repo = $this->em->getRepository(KeyFinanceDates::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(KeyFinanceDates::class), $id);
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

            $type = $params['type'] ? (int)$params['type'] : 0;

            $keyFinanceDates = new KeyFinanceDates();
            $keyFinanceDates->setType($type);
            $keyFinanceDates->setTitle($params['title']);

            $date = null;
            if (!empty($params['date'])) {
                $date = new \DateTime($params['date']);
            }

            $keyFinanceDates->setDate($date);

            $keyFinanceDates->setDescription($params['description']);
            $keyFinanceDates->setSpace($space);

            $this->validate($keyFinanceDates, null, ['api_admin_key_finance_dates_add']);

            $this->em->persist($keyFinanceDates);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $keyFinanceDates->getId();
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

            /** @var KeyFinanceDatesRepository $repo */
            $repo = $this->em->getRepository(KeyFinanceDates::class);

            /** @var KeyFinanceDates $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(KeyFinanceDates::class), $id);

            if ($entity === null) {
                throw new KeyFinanceDatesNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);

            $date = null;
            if (!empty($params['date'])) {
                $date = new \DateTime($params['date']);
            }

            $entity->setDate($date);

            $entity->setDescription($params['description']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_key_finance_dates_edit']);

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

            /** @var KeyFinanceDatesRepository $repo */
            $repo = $this->em->getRepository(KeyFinanceDates::class);

            /** @var KeyFinanceDates $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(KeyFinanceDates::class), $id);

            if ($entity === null) {
                throw new KeyFinanceDatesNotFoundException();
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
                throw new KeyFinanceDatesNotFoundException();
            }

            /** @var KeyFinanceDatesRepository $repo */
            $repo = $this->em->getRepository(KeyFinanceDates::class);

            $data = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(KeyFinanceDates::class), $ids);

            if (empty($data)) {
                throw new KeyFinanceDatesNotFoundException();
            }

            /**
             * @var KeyFinanceDates $keyFinanceDates
             */
            foreach ($data as $keyFinanceDates) {
                $this->em->remove($keyFinanceDates);
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
            throw new KeyFinanceDatesNotFoundException();
        }

        /** @var KeyFinanceDatesRepository $repo */
        $repo = $this->em->getRepository(KeyFinanceDates::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(KeyFinanceDates::class), $ids);

        if (empty($entities)) {
            throw new KeyFinanceDatesNotFoundException();
        }

        return $this->getRelatedData(KeyFinanceDates::class, $entities);
    }
}
