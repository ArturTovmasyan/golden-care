<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\KeyFinanceTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentLedgerNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentKeyFinanceDateNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\KeyFinanceType;
use App\Entity\ResidentKeyFinanceDate;
use App\Entity\ResidentLedger;
use App\Repository\KeyFinanceTypeRepository;
use App\Repository\ResidentKeyFinanceDateRepository;
use App\Repository\ResidentLedgerRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentKeyFinanceDateService
 * @package App\Api\V1\Admin\Service
 */
class ResidentKeyFinanceDateService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        if (empty($params) || empty($params[0]['ledger_id'])) {
            throw new ResidentLedgerNotFoundException();
        }

        $ledgerId = $params[0]['ledger_id'];

        $queryBuilder
            ->where('rkfd.ledger = :ledgerId')
            ->setParameter('ledgerId', $ledgerId);

        /** @var ResidentKeyFinanceDateRepository $repo */
        $repo = $this->em->getRepository(ResidentKeyFinanceDate::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentKeyFinanceDate::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['ledger_id'])) {
            $ledgerId = $params[0]['ledger_id'];

            /** @var ResidentKeyFinanceDateRepository $repo */
            $repo = $this->em->getRepository(ResidentKeyFinanceDate::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentKeyFinanceDate::class), $ledgerId);
        }

        throw new ResidentLedgerNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentKeyFinanceDate|null|object
     */
    public function getById($id)
    {
        /** @var ResidentKeyFinanceDateRepository $repo */
        $repo = $this->em->getRepository(ResidentKeyFinanceDate::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentKeyFinanceDate::class), $id);
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

            $currentSpace = $this->grantService->getCurrentSpace();

            $ledgerId = $params['ledger_id'] ?? 0;

            /** @var ResidentLedgerRepository $residentLedgerRepo */
            $residentLedgerRepo = $this->em->getRepository(ResidentLedger::class);

            /** @var ResidentLedger $ledger */
            $ledger = $residentLedgerRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $ledgerId);

            if ($ledger === null) {
                throw new ResidentLedgerNotFoundException();
            }

            $keyFinanceTypeId = $params['key_finance_type_id'] ?? 0;

            /** @var KeyFinanceTypeRepository $keyFinanceTypeRepo */
            $keyFinanceTypeRepo = $this->em->getRepository(KeyFinanceType::class);

            /** @var KeyFinanceType $keyFinanceType */
            $keyFinanceType = $keyFinanceTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(KeyFinanceType::class), $keyFinanceTypeId);

            if ($keyFinanceType === null) {
                throw new KeyFinanceTypeNotFoundException();
            }

            $residentKeyFinanceDate = new ResidentKeyFinanceDate();
            $residentKeyFinanceDate->setLedger($ledger);
            $residentKeyFinanceDate->setKeyFinanceType($keyFinanceType);

            $date = null;
            if (!empty($params['date'])) {
                $date = new \DateTime($params['date']);
            }

            $residentKeyFinanceDate->setDate($date);

            $this->validate($residentKeyFinanceDate, null, ['api_admin_resident_key_finance_date_add']);

            $this->em->persist($residentKeyFinanceDate);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentKeyFinanceDate->getId();
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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentKeyFinanceDateRepository $repo */
            $repo = $this->em->getRepository(ResidentKeyFinanceDate::class);

            /** @var ResidentKeyFinanceDate $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentKeyFinanceDate::class), $id);

            if ($entity === null) {
                throw new ResidentKeyFinanceDateNotFoundException();
            }

            $ledgerId = $params['ledger_id'] ?? 0;

            /** @var ResidentLedgerRepository $residentLedgerRepo */
            $residentLedgerRepo = $this->em->getRepository(ResidentLedger::class);

            /** @var ResidentLedger $ledger */
            $ledger = $residentLedgerRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $ledgerId);

            if ($ledger === null) {
                throw new ResidentLedgerNotFoundException();
            }

            $entity->setLedger($ledger);

            $date = null;
            if (!empty($params['date'])) {
                $date = new \DateTime($params['date']);
            }

            $entity->setDate($date);

            $this->validate($entity, null, ['api_admin_resident_key_finance_date_edit']);

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

            /** @var ResidentKeyFinanceDateRepository $repo */
            $repo = $this->em->getRepository(ResidentKeyFinanceDate::class);

            /** @var ResidentKeyFinanceDate $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentKeyFinanceDate::class), $id);

            if ($entity === null) {
                throw new ResidentKeyFinanceDateNotFoundException();
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
                throw new ResidentKeyFinanceDateNotFoundException();
            }

            /** @var ResidentKeyFinanceDateRepository $repo */
            $repo = $this->em->getRepository(ResidentKeyFinanceDate::class);

            $residentKeyFinanceDates = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentKeyFinanceDate::class), $ids);

            if (empty($residentKeyFinanceDates)) {
                throw new ResidentKeyFinanceDateNotFoundException();
            }

            /**
             * @var ResidentKeyFinanceDate $residentKeyFinanceDate
             */
            foreach ($residentKeyFinanceDates as $residentKeyFinanceDate) {
                $this->em->remove($residentKeyFinanceDate);
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
            throw new ResidentKeyFinanceDateNotFoundException();
        }

        /** @var ResidentKeyFinanceDateRepository $repo */
        $repo = $this->em->getRepository(ResidentKeyFinanceDate::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentKeyFinanceDate::class), $ids);

        if (empty($entities)) {
            throw new ResidentKeyFinanceDateNotFoundException();
        }

        return $this->getRelatedData(ResidentKeyFinanceDate::class, $entities);
    }
}
