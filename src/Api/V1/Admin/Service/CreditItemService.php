<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CanBeChangedIsRequiredException;
use App\Api\V1\Common\Service\Exception\CreditItemNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CreditItem;
use App\Entity\ResidentLedger;
use App\Entity\Space;
use App\Repository\CreditItemRepository;
use App\Repository\ResidentLedgerRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CreditItemService
 * @package App\Api\V1\Admin\Service
 */
class CreditItemService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var CreditItemRepository $repo */
        $repo = $this->em->getRepository(CreditItem::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CreditItem::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        $validThroughDate = null;
        if (!empty($params) || !empty($params[0]['ledger_id'])) {
            /** @var ResidentLedgerRepository $ledgerRepo */
            $ledgerRepo = $this->em->getRepository(ResidentLedger::class);
            /** @var ResidentLedger $ledger */
            $ledger = $ledgerRepo->find($params[0]['ledger_id']);

            if ($ledger->getCreatedAt() !== null) {
                $validThroughDate = new \DateTime($ledger->getCreatedAt()->format('Y-m-01 00:00:00'));
            }
        }

        /** @var CreditItemRepository $repo */
        $repo = $this->em->getRepository(CreditItem::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CreditItem::class), $validThroughDate);
    }

    /**
     * @param $id
     * @return CreditItem|null|object
     */
    public function getById($id)
    {
        /** @var CreditItemRepository $repo */
        $repo = $this->em->getRepository(CreditItem::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CreditItem::class), $id);
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

            $creditItem = new CreditItem();
            $creditItem->setTitle($params['title']);
            $creditItem->setSpace($space);

            $amount = !empty($params['amount']) ? $params['amount'] : null;
            $creditItem->setAmount($amount);

            $canBeChanged = !empty($params['can_be_changed']) ? (bool)$params['can_be_changed'] : false;
            if (is_numeric($amount) && $canBeChanged === false) {
                throw new CanBeChangedIsRequiredException();
            }
            $creditItem->setCanBeChanged($canBeChanged);

            $date = $params['valid_through_date'];
            if (!empty($date)) {
                $date = new \DateTime($params['valid_through_date']);
                $date->setTime(0, 0, 0);

                $creditItem->setValidThroughDate($date);
            } else {
                $creditItem->setValidThroughDate(null);
            }

            $this->validate($creditItem, null, ['api_admin_credit_item_add']);

            $this->em->persist($creditItem);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $creditItem->getId();
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

            /** @var CreditItemRepository $repo */
            $repo = $this->em->getRepository(CreditItem::class);

            /** @var CreditItem $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CreditItem::class), $id);

            if ($entity === null) {
                throw new CreditItemNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $amount = !empty($params['amount']) ? $params['amount'] : null;
            $entity->setAmount($amount);

            $canBeChanged = !empty($params['can_be_changed']) ? (bool)$params['can_be_changed'] : false;
            if (is_numeric($amount) && $canBeChanged === false) {
                throw new CanBeChangedIsRequiredException();
            }
            $entity->setCanBeChanged($canBeChanged);

            $date = $params['valid_through_date'];
            if (!empty($date)) {
                $date = new \DateTime($params['valid_through_date']);
                $date->setTime(0, 0, 0);

                $entity->setValidThroughDate($date);
            } else {
                $entity->setValidThroughDate(null);
            }

            $this->validate($entity, null, ['api_admin_credit_item_edit']);

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

            /** @var CreditItemRepository $repo */
            $repo = $this->em->getRepository(CreditItem::class);

            /** @var CreditItem $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CreditItem::class), $id);

            if ($entity === null) {
                throw new CreditItemNotFoundException();
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
                throw new CreditItemNotFoundException();
            }

            /** @var CreditItemRepository $repo */
            $repo = $this->em->getRepository(CreditItem::class);

            $creditItems = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CreditItem::class), $ids);

            if (empty($creditItems)) {
                throw new CreditItemNotFoundException();
            }

            /**
             * @var CreditItem $creditItem
             */
            foreach ($creditItems as $creditItem) {
                $this->em->remove($creditItem);
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
            throw new CreditItemNotFoundException();
        }

        /** @var CreditItemRepository $repo */
        $repo = $this->em->getRepository(CreditItem::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CreditItem::class), $ids);

        if (empty($entities)) {
            throw new CreditItemNotFoundException();
        }

        return $this->getRelatedData(CreditItem::class, $entities);
    }
}