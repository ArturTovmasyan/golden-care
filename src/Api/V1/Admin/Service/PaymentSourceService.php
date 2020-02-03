<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\BaseRateNotBeBlankException;
use App\Api\V1\Common\Service\Exception\CareLevelNotFoundException;
use App\Api\V1\Common\Service\Exception\PaymentSourceNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CareLevel;
use App\Entity\PaymentSource;
use App\Entity\ResidentRent;
use App\Entity\PaymentSourceBaseRate;
use App\Entity\Space;
use App\Repository\CareLevelRepository;
use App\Repository\PaymentSourceRepository;
use App\Repository\ResidentRentRepository;
use App\Repository\PaymentSourceBaseRateRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PaymentSourceService
 * @package App\Api\V1\Admin\Service
 */
class PaymentSourceService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var PaymentSourceRepository $repo */
        $repo = $this->em->getRepository(PaymentSource::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var PaymentSourceRepository $repo */
        $repo = $this->em->getRepository(PaymentSource::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSource::class));
    }

    /**
     * @param $id
     * @return PaymentSource|null|object
     */
    public function getById($id)
    {
        /** @var PaymentSourceRepository $repo */
        $repo = $this->em->getRepository(PaymentSource::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Exception
     */
    public function add(array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $period = $params['period'] ? (int)$params['period'] : 0;
            $careLevelAdjustment = (bool)$params['care_level_adjustment'];

            $paymentSource = new PaymentSource();
            $paymentSource->setTitle($params['title']);
            $paymentSource->setAwayReduction((bool)$params['away_reduction']);
            $paymentSource->setPeriod($period);
            $paymentSource->setAmount($params['amount']);
            $paymentSource->setCareLevelAdjustment($careLevelAdjustment);
            $paymentSource->setSpace($space);

            $baseRates = $this->saveBaseRates($currentSpace, $paymentSource, $careLevelAdjustment === true && $params['base_rates'] ? $params['base_rates'] : []);

            if ($careLevelAdjustment === true && \count($baseRates) < 1) {
                throw new BaseRateNotBeBlankException();
            }

            $paymentSource->setBaseRates($baseRates);

            $this->validate($paymentSource, null, ['api_admin_payment_source_add']);

            $this->em->persist($paymentSource);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $paymentSource->getId();
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

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var PaymentSourceRepository $repo */
            $repo = $this->em->getRepository(PaymentSource::class);

            /** @var PaymentSource $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $id);

            if ($entity === null) {
                throw new PaymentSourceNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $period = $params['period'] ? (int)$params['period'] : 0;
            $careLevelAdjustment = (bool)$params['care_level_adjustment'];

            $entity->setTitle($params['title']);
            $entity->setAwayReduction((bool)$params['away_reduction']);
            $entity->setPeriod($period);
            $entity->setAmount($params['amount']);
            $entity->setCareLevelAdjustment($careLevelAdjustment);
            $entity->setSpace($space);

            $baseRates = $this->saveBaseRates($currentSpace, $entity, $careLevelAdjustment === true && $params['base_rates'] ? $params['base_rates'] : []);

            if ($careLevelAdjustment === true && \count($baseRates) < 1) {
                throw new BaseRateNotBeBlankException();
            }

            $entity->setBaseRates($baseRates);

            $this->validate($entity, null, ['api_admin_payment_source_edit']);

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
     * @param PaymentSource $paymentSource
     * @param array $baseRates
     * @return array|null
     */
    private function saveBaseRates($currentSpace, PaymentSource $paymentSource, array $baseRates = []): ?array
    {
        $validationGroup = 'api_admin_source_base_rate_add';
        if ($paymentSource->getId() !== null) {
            $validationGroup = 'api_admin_source_base_rate_edit';

            /** @var  PaymentSourceBaseRateRepository $baseRateRepo */
            $baseRateRepo = $this->em->getRepository(PaymentSourceBaseRate::class);

            $oldRates = $baseRateRepo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSourceBaseRate::class), $paymentSource);

            foreach ($oldRates as $rate) {
                $this->em->remove($rate);
            }
        }

        $paymentSourceBaseRates = [];

        foreach ($baseRates as $baseRate) {
            $careLevelId = $baseRate['care_level_id'] ?? 0;

            /** @var CareLevelRepository $careLevelRepo */
            $careLevelRepo = $this->em->getRepository(CareLevel::class);

            /** @var CareLevel $careLevel */
            $careLevel = $careLevelRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $careLevelId);

            if ($careLevel === null) {
                throw new CareLevelNotFoundException();
            }

            $amount = !empty($baseRate['amount']) ? $baseRate['amount'] : 0;

            $paymentSourceBaseRate = new PaymentSourceBaseRate();
            $paymentSourceBaseRate->setPaymentSource($paymentSource);
            $paymentSourceBaseRate->setCareLevel($careLevel);
            $paymentSourceBaseRate->setAmount($amount);

            $this->validate($paymentSourceBaseRate, null, [$validationGroup]);

            $this->em->persist($paymentSourceBaseRate);

            $paymentSourceBaseRates[] = $paymentSourceBaseRate;
        }

        return $paymentSourceBaseRates;
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var PaymentSourceRepository $repo */
            $repo = $this->em->getRepository(PaymentSource::class);

            /** @var PaymentSource $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $id);

            if ($entity === null) {
                throw new PaymentSourceNotFoundException();
            }

            /** @var ResidentRentRepository $rentRepo */
            $rentRepo = $this->em->getRepository(ResidentRent::class);

            $rents = $rentRepo->getEntityWithSources($this->grantService->getCurrentSpace(), null);

            if (!empty($rents)) {
                /** @var ResidentRent $rent */
                foreach ($rents as $rent) {
                    if (!empty($rent->getSource())) {
                        $sources = $rent->getSource();
                        foreach ($sources as $key => $source) {
                            if ($source['id'] === $id) {
                                unset($sources[$key]);
                                $changedSources = array_values($sources);
                                $rent->setSource($changedSources);
                            }
                        }

                        $this->em->persist($rent);
                    }
                }
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
                throw new PaymentSourceNotFoundException();
            }

            /** @var PaymentSourceRepository $repo */
            $repo = $this->em->getRepository(PaymentSource::class);

            $paymentSources = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $ids);

            if (empty($paymentSources)) {
                throw new PaymentSourceNotFoundException();
            }

            $ids = array_map(function ($item) {
                return $item->getId();
            }, $paymentSources);

            /** @var ResidentRentRepository $rentRepo */
            $rentRepo = $this->em->getRepository(ResidentRent::class);

            $rents = $rentRepo->getEntityWithSources($this->grantService->getCurrentSpace(), null);

            if (!empty($rents)) {
                foreach ($ids as $id) {
                    /** @var ResidentRent $rent */
                    foreach ($rents as $rent) {
                        if (!empty($rent->getSource())) {
                            $sources = $rent->getSource();
                            foreach ($sources as $key => $source) {
                                if ($source['id'] === $id) {
                                    unset($sources[$key]);
                                    $changedSources = array_values($sources);
                                    $rent->setSource($changedSources);
                                }
                            }

                            $this->em->persist($rent);
                        }
                    }
                }
            }

            /**
             * @var PaymentSource $paymentSource
             */
            foreach ($paymentSources as $paymentSource) {
                $this->em->remove($paymentSource);
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
            throw new PaymentSourceNotFoundException();
        }

        /** @var PaymentSourceRepository $repo */
        $repo = $this->em->getRepository(PaymentSource::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $ids);

        if (empty($entities)) {
            throw new PaymentSourceNotFoundException();
        }

        $ids = array_map(function ($item) {
            return $item->getId();
        }, $entities);

        /** @var ResidentRentRepository $rentRepo */
        $rentRepo = $this->em->getRepository(ResidentRent::class);

        $rents = $rentRepo->getWithSources($this->grantService->getCurrentSpace(), null);

        $result = $this->getRelatedData(PaymentSource::class, $entities);

        if (!empty($rents)) {
            $residentRents = [];
            foreach ($ids as $id) {
                foreach ($rents as $rent) {
                    if (!empty($rent['source'])) {
                        foreach ($rent['source'] as $source) {
                            if ((int)$source['id'] === $id) {
                                $residentRents[$id][] = [
                                    'amount' => $source['amount'],
                                ];
                            }
                        }
                    }
                }

                $currentRents = array_key_exists($id, $residentRents) ? $residentRents[$id] : [];

                $result[$id][0] = [
                    'targetEntity' => ResidentRent::class,
                    'residentRents' => $currentRents,
                    'count' => \count($currentRents),
                ];

                $result[$id]['sum'] = $result[$id][0]['count'];
            }
        }

        return $result;
    }
}
