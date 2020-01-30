<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\RentReasonNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentRentIncreaseNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\RentReason;
use App\Entity\Resident;
use App\Entity\ResidentRentIncrease;
use App\Repository\RentReasonRepository;
use App\Repository\ResidentRentIncreaseRepository;
use App\Repository\ResidentRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentRentIncreaseService
 * @package App\Api\V1\Admin\Service
 */
class ResidentRentIncreaseService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rri.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentRentIncreaseRepository $repo */
        $repo = $this->em->getRepository(ResidentRentIncrease::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentRentIncrease::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentRentIncreaseRepository $repo */
            $repo = $this->em->getRepository(ResidentRentIncrease::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentRentIncrease::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentRentIncrease|null|object
     */
    public function getById($id)
    {
        /** @var ResidentRentIncreaseRepository $repo */
        $repo = $this->em->getRepository(ResidentRentIncrease::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentRentIncrease::class), $id);
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

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $reasonId = $params['reason_id'] ?? 0;

            /** @var RentReasonRepository $reasonRepo */
            $reasonRepo = $this->em->getRepository(RentReason::class);

            /** @var RentReason $reason */
            $reason = $reasonRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(RentReason::class), $reasonId);

            if ($reason === null) {
                throw new RentReasonNotFoundException();
            }

            $residentRentIncrease = new ResidentRentIncrease();
            $residentRentIncrease->setResident($resident);
            $residentRentIncrease->setReason($reason);
            $residentRentIncrease->setAmount($params['amount']);

            $effectiveDate = null;
            if (!empty($params['effective_date'])) {
                $effectiveDate = new \DateTime($params['effective_date']);
            }

            $residentRentIncrease->setEffectiveDate($effectiveDate);

            $notificationDate = null;
            if (!empty($params['notification_date'])) {
                $notificationDate = new \DateTime($params['notification_date']);
            }

            $residentRentIncrease->setNotificationDate($notificationDate);

            $this->validate($residentRentIncrease, null, ['api_admin_resident_rent_increase_add']);

            $this->em->persist($residentRentIncrease);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentRentIncrease->getId();
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

            /** @var ResidentRentIncreaseRepository $repo */
            $repo = $this->em->getRepository(ResidentRentIncrease::class);

            /** @var ResidentRentIncrease $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRentIncrease::class), $id);

            if ($entity === null) {
                throw new ResidentRentIncreaseNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $reasonId = $params['reason_id'] ?? 0;

            /** @var RentReasonRepository $reasonRepo */
            $reasonRepo = $this->em->getRepository(RentReason::class);

            /** @var RentReason $reason */
            $reason = $reasonRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(RentReason::class), $reasonId);

            if ($reason === null) {
                throw new RentReasonNotFoundException();
            }

            $entity->setResident($resident);
            $entity->setReason($reason);
            $entity->setAmount($params['amount']);

            $effectiveDate = null;
            if (!empty($params['effective_date'])) {
                $effectiveDate = new \DateTime($params['effective_date']);
            }

            $entity->setEffectiveDate($effectiveDate);

            $notificationDate = null;
            if (!empty($params['notification_date'])) {
                $notificationDate = new \DateTime($params['notification_date']);
            }

            $entity->setNotificationDate($notificationDate);

            $this->validate($entity, null, ['api_admin_resident_rent_increase_edit']);

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

            /** @var ResidentRentIncreaseRepository $repo */
            $repo = $this->em->getRepository(ResidentRentIncrease::class);

            /** @var ResidentRentIncrease $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentRentIncrease::class), $id);

            if ($entity === null) {
                throw new ResidentRentIncreaseNotFoundException();
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
                throw new ResidentRentIncreaseNotFoundException();
            }

            /** @var ResidentRentIncreaseRepository $repo */
            $repo = $this->em->getRepository(ResidentRentIncrease::class);

            $residentRentIncreases = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentRentIncrease::class), $ids);

            if (empty($residentRentIncreases)) {
                throw new ResidentRentIncreaseNotFoundException();
            }

            /**
             * @var ResidentRentIncrease $residentRentIncrease
             */
            foreach ($residentRentIncreases as $residentRentIncrease) {
                $this->em->remove($residentRentIncrease);
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
            throw new ResidentRentIncreaseNotFoundException();
        }

        /** @var ResidentRentIncreaseRepository $repo */
        $repo = $this->em->getRepository(ResidentRentIncrease::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentRentIncrease::class), $ids);

        if (empty($entities)) {
            throw new ResidentRentIncreaseNotFoundException();
        }

        return $this->getRelatedData(ResidentRentIncrease::class, $entities);
    }
}
