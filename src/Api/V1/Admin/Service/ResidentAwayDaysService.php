<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\InvalidEffectiveDateException;
use App\Api\V1\Common\Service\Exception\ResidentLedgerNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentAwayDaysNotFoundException;
use App\Api\V1\Common\Service\Exception\StartGreaterEndDateException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ResidentAwayDays;
use App\Entity\ResidentLedger;
use App\Repository\ResidentAwayDaysRepository;
use App\Repository\ResidentLedgerRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentAwayDaysService
 * @package App\Api\V1\Admin\Service
 */
class ResidentAwayDaysService extends BaseService implements IGridService
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
            ->where('rad.ledger = :ledgerId')
            ->setParameter('ledgerId', $ledgerId);

        /** @var ResidentAwayDaysRepository $repo */
        $repo = $this->em->getRepository(ResidentAwayDays::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAwayDays::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['ledger_id'])) {
            $ledgerId = $params[0]['ledger_id'];

            /** @var ResidentAwayDaysRepository $repo */
            $repo = $this->em->getRepository(ResidentAwayDays::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAwayDays::class), $ledgerId);
        }

        throw new ResidentLedgerNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentAwayDays|null|object
     */
    public function getById($id)
    {
        /** @var ResidentAwayDaysRepository $repo */
        $repo = $this->em->getRepository(ResidentAwayDays::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAwayDays::class), $id);
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

            $residentAwayDays = new ResidentAwayDays();
            $residentAwayDays->setLedger($ledger);

            $start = null;
            if (!empty($params['start'])) {
                $start = new \DateTime($params['start']);
                $start->setTime(0, 0, 0);

                if ($ledger->getCreatedAt()->format('Y') !== $start->format('Y') || $ledger->getCreatedAt()->format('m') !== $start->format('m')) {
                    throw new InvalidEffectiveDateException();
                }
            }

            $residentAwayDays->setStart($start);

            $end = null;
            if (!empty($params['end'])) {
                $end = new \DateTime($params['end']);
                $end->setTime(23, 59, 59);

                if ($start > $end) {
                    throw new StartGreaterEndDateException();
                }

                if ($ledger->getCreatedAt()->format('Y') !== $end->format('Y') || $ledger->getCreatedAt()->format('m') !== $end->format('m')) {
                    throw new InvalidEffectiveDateException();
                }
            }

            $residentAwayDays->setEnd($end);
            $residentAwayDays->setReason($params['reason']);

            $this->validate($residentAwayDays, null, ['api_admin_resident_away_days_add']);

            $this->em->persist($residentAwayDays);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentAwayDays->getId();
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

            /** @var ResidentAwayDaysRepository $repo */
            $repo = $this->em->getRepository(ResidentAwayDays::class);

            /** @var ResidentAwayDays $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAwayDays::class), $id);

            if ($entity === null) {
                throw new ResidentAwayDaysNotFoundException();
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

            $start = null;
            if (!empty($params['start'])) {
                $start = new \DateTime($params['start']);
                $start->setTime(0, 0, 0);

                if ($ledger->getCreatedAt()->format('Y') !== $start->format('Y') || $ledger->getCreatedAt()->format('m') !== $start->format('m')) {
                    throw new InvalidEffectiveDateException();
                }
            }

            $entity->setStart($start);

            $end = null;
            if (!empty($params['end'])) {
                $end = new \DateTime($params['end']);
                $end->setTime(23, 59, 59);

                if ($start > $end) {
                    throw new StartGreaterEndDateException();
                }

                if ($ledger->getCreatedAt()->format('Y') !== $end->format('Y') || $ledger->getCreatedAt()->format('m') !== $end->format('m')) {
                    throw new InvalidEffectiveDateException();
                }
            }

            $entity->setEnd($end);
            $entity->setReason($params['reason']);

            $this->validate($entity, null, ['api_admin_resident_away_days_edit']);

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

            /** @var ResidentAwayDaysRepository $repo */
            $repo = $this->em->getRepository(ResidentAwayDays::class);

            /** @var ResidentAwayDays $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAwayDays::class), $id);

            if ($entity === null) {
                throw new ResidentAwayDaysNotFoundException();
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
                throw new ResidentAwayDaysNotFoundException();
            }

            /** @var ResidentAwayDaysRepository $repo */
            $repo = $this->em->getRepository(ResidentAwayDays::class);

            $data = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAwayDays::class), $ids);

            if (empty($data)) {
                throw new ResidentAwayDaysNotFoundException();
            }

            /**
             * @var ResidentAwayDays $residentAwayDays
             */
            foreach ($data as $residentAwayDays) {
                $this->em->remove($residentAwayDays);
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
            throw new ResidentAwayDaysNotFoundException();
        }

        /** @var ResidentAwayDaysRepository $repo */
        $repo = $this->em->getRepository(ResidentAwayDays::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAwayDays::class), $ids);

        if (empty($entities)) {
            throw new ResidentAwayDaysNotFoundException();
        }

        return $this->getRelatedData(ResidentAwayDays::class, $entities);
    }
}
