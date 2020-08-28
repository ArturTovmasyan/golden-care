<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CreditNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Credit;
use App\Entity\Space;
use App\Repository\CreditRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CreditService
 * @package App\Api\V1\Admin\Service
 */
class CreditService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var CreditRepository $repo */
        $repo = $this->em->getRepository(Credit::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Credit::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var CreditRepository $repo */
        $repo = $this->em->getRepository(Credit::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Credit::class));
    }

    /**
     * @param $id
     * @return Credit|null|object
     */
    public function getById($id)
    {
        /** @var CreditRepository $repo */
        $repo = $this->em->getRepository(Credit::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Credit::class), $id);
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

            $credit = new Credit();
            $credit->setTitle($params['title']);
            $credit->setSpace($space);

            $this->validate($credit, null, ['api_admin_credit_add']);

            $this->em->persist($credit);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $credit->getId();
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

            /** @var CreditRepository $repo */
            $repo = $this->em->getRepository(Credit::class);

            /** @var Credit $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Credit::class), $id);

            if ($entity === null) {
                throw new CreditNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_credit_edit']);

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

            /** @var CreditRepository $repo */
            $repo = $this->em->getRepository(Credit::class);

            /** @var Credit $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Credit::class), $id);

            if ($entity === null) {
                throw new CreditNotFoundException();
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
                throw new CreditNotFoundException();
            }

            /** @var CreditRepository $repo */
            $repo = $this->em->getRepository(Credit::class);

            $credits = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Credit::class), $ids);

            if (empty($credits)) {
                throw new CreditNotFoundException();
            }

            /**
             * @var Credit $credit
             */
            foreach ($credits as $credit) {
                $this->em->remove($credit);
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
            throw new CreditNotFoundException();
        }

        /** @var CreditRepository $repo */
        $repo = $this->em->getRepository(Credit::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Credit::class), $ids);

        if (empty($entities)) {
            throw new CreditNotFoundException();
        }

        return $this->getRelatedData(Credit::class, $entities);
    }
}
