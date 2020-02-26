<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\BaseRateNotBeBlankException;
use App\Api\V1\Common\Service\Exception\BaseRateNotFoundException;
use App\Api\V1\Common\Service\Exception\CareLevelNotFoundException;
use App\Api\V1\Common\Service\Exception\PaymentSourceDuplicateBaseRateByDateException;
use App\Api\V1\Common\Service\Exception\PaymentSourceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CareLevel;
use App\Entity\PaymentSource;
use App\Entity\PaymentSourceBaseRate;
use App\Entity\PaymentSourceBaseRateCareLevel;
use App\Repository\CareLevelRepository;
use App\Repository\PaymentSourceBaseRateCareLevelRepository;
use App\Repository\PaymentSourceBaseRateRepository;
use App\Repository\PaymentSourceRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PaymentSourceBaseRateService
 * @package App\Api\V1\Admin\Service
 */
class PaymentSourceBaseRateService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        $paymentSourceId = null;
        if (!empty($params) || !empty($params[0]['payment_source_id'])) {
            $paymentSourceId = $params[0]['payment_source_id'];
        }

        /** @var PaymentSourceBaseRateRepository $repo */
        $repo = $this->em->getRepository(PaymentSourceBaseRate::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSourceBaseRate::class), $queryBuilder, $paymentSourceId);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        $paymentSourceId = null;
        if (!empty($params) || !empty($params[0]['payment_source_id'])) {
            $paymentSourceId = $params[0]['payment_source_id'];
        }

        /** @var PaymentSourceBaseRateRepository $repo */
        $repo = $this->em->getRepository(PaymentSourceBaseRate::class);

        return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSourceBaseRate::class), $paymentSourceId);
    }

    /**
     * @param $id
     * @return PaymentSourceBaseRate|null|object
     */
    public function getById($id)
    {
        /** @var PaymentSourceBaseRateRepository $repo */
        $repo = $this->em->getRepository(PaymentSourceBaseRate::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSourceBaseRate::class), $id);
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

            $paymentSourceId = $params['payment_source_id'] ?? 0;

            /** @var PaymentSourceRepository $paymentSourceRepo */
            $paymentSourceRepo = $this->em->getRepository(PaymentSource::class);

            /** @var PaymentSource $paymentSource */
            $paymentSource = $paymentSourceRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $paymentSourceId);

            if ($paymentSource === null) {
                throw new PaymentSourceNotFoundException();
            }

            $baseRate = new PaymentSourceBaseRate();
            $baseRate->setPaymentSource($paymentSource);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
                $dateFormatted = $date->format('Y-m-d 00:00:00');

                $date = new \DateTime($dateFormatted);

                /** @var PaymentSourceBaseRateRepository $repo */
                $repo = $this->em->getRepository(PaymentSourceBaseRate::class);

                /** @var PaymentSourceBaseRate $existingPaymentSourceBaseRate */
                $existingBaseRates = $repo->getByDate($currentSpace, $this->grantService->getCurrentUserEntityGrants(PaymentSourceBaseRate::class), $paymentSourceId, $date);

                if (!empty($existingBaseRates)) {
                    throw new PaymentSourceDuplicateBaseRateByDateException();
                }
            }

            $baseRate->setDate($date);

            $careLevelAdjustment = $paymentSource->isCareLevelAdjustment();

            $levels = $this->saveLevels($currentSpace, $baseRate, $careLevelAdjustment && !empty($params['levels']) ? $params['levels'] : []);

            if ($careLevelAdjustment && \count($levels) < 1) {
                throw new BaseRateNotBeBlankException();
            }

            $baseRate->setLevels($levels);

            $this->validate($baseRate, null, ['api_admin_payment_source_base_rate_add']);

            $this->em->persist($baseRate);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $baseRate->getId();
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

            /** @var PaymentSourceBaseRateRepository $repo */
            $repo = $this->em->getRepository(PaymentSourceBaseRate::class);

            /** @var PaymentSourceBaseRate $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(PaymentSourceBaseRate::class), $id);

            if ($entity === null) {
                throw new BaseRateNotFoundException();
            }

            $paymentSourceId = $params['payment_source_id'] ?? 0;

            /** @var PaymentSourceRepository $paymentSourceRepo */
            $paymentSourceRepo = $this->em->getRepository(PaymentSource::class);

            /** @var PaymentSource $paymentSource */
            $paymentSource = $paymentSourceRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $paymentSourceId);

            if ($paymentSource === null) {
                throw new PaymentSourceNotFoundException();
            }

            $entity->setPaymentSource($paymentSource);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
                $dateFormatted = $date->format('Y-m-d 00:00:00');

                $date = new \DateTime($dateFormatted);
            }

            $entity->setDate($date);

            $careLevelAdjustment = $paymentSource->isCareLevelAdjustment();

            $levels = $this->saveLevels($currentSpace, $entity, $careLevelAdjustment === true && $params['levels'] ? $params['levels'] : []);

            if ($careLevelAdjustment && \count($levels) < 1) {
                throw new BaseRateNotBeBlankException();
            }

            $entity->setLevels($levels);

            $this->validate($entity, null, ['api_admin_payment_source_base_rate_edit']);

            $this->em->persist($entity);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $currentSpace
     * @param PaymentSourceBaseRate $baseRate
     * @param array $baseRates
     * @return array|null
     */
    private function saveLevels($currentSpace, PaymentSourceBaseRate $baseRate, array $baseRates = []): ?array
    {
        if ($baseRate->getId() !== null) {
            /** @var PaymentSourceBaseRateCareLevelRepository $levelRepo */
            $levelRepo = $this->em->getRepository(PaymentSourceBaseRateCareLevel::class);

            $oldLevels = $levelRepo->getBy($baseRate->getId());

            foreach ($oldLevels as $oldLevel) {
                $this->em->remove($oldLevel);
            }
        }

        $baseRateLevels = [];

        foreach ($baseRates as $rate) {
            $careLevelId = $rate['care_level_id'] ?? 0;

            /** @var CareLevelRepository $careLevelRepo */
            $careLevelRepo = $this->em->getRepository(CareLevel::class);

            /** @var CareLevel $careLevel */
            $careLevel = $careLevelRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $careLevelId);

            if ($careLevel === null) {
                throw new CareLevelNotFoundException();
            }

            $amount = !empty($rate['amount']) ? $rate['amount'] : 0;

            $level = new PaymentSourceBaseRateCareLevel();
            $level->setBaseRate($baseRate);
            $level->setCareLevel($careLevel);
            $level->setAmount($amount);

            $this->em->persist($level);

            $baseRateLevels[] = $level;
        }

        return $baseRateLevels;
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var PaymentSourceBaseRateRepository $repo */
            $repo = $this->em->getRepository(PaymentSourceBaseRate::class);

            /** @var PaymentSourceBaseRate $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSourceBaseRate::class), $id);

            if ($entity === null) {
                throw new BaseRateNotFoundException();
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
                throw new BaseRateNotFoundException();
            }

            /** @var PaymentSourceBaseRateRepository $repo */
            $repo = $this->em->getRepository(PaymentSourceBaseRate::class);

            $baseRates = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSourceBaseRate::class), $ids);

            if (empty($baseRates)) {
                throw new BaseRateNotFoundException();
            }

            /**
             * @var PaymentSourceBaseRate $baseRate
             */
            foreach ($baseRates as $baseRate) {
                $this->em->remove($baseRate);
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
            throw new BaseRateNotFoundException();
        }

        /** @var PaymentSourceBaseRateRepository $repo */
        $repo = $this->em->getRepository(PaymentSourceBaseRate::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSourceBaseRate::class), $ids);

        if (empty($entities)) {
            throw new BaseRateNotFoundException();
        }

        return $this->getRelatedData(PaymentSourceBaseRate::class, $entities);
    }
}
