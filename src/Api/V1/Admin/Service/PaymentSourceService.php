<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\MissingBaseRateForCareLevelException;
use App\Api\V1\Common\Service\Exception\PaymentSourceNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CareLevel;
use App\Entity\PaymentSource;
use App\Entity\PaymentSourceBaseRate;
use App\Entity\ResidentLedger;
use App\Entity\ResidentRent;
use App\Entity\Space;
use App\Repository\CareLevelRepository;
use App\Repository\PaymentSourceRepository;
use App\Repository\ResidentLedgerRepository;
use App\Repository\ResidentRentRepository;
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

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $period = $params['period'] ? (int)$params['period'] : 0;
            $careLevelAdjustment = (bool)$params['care_level_adjustment'];

            $paymentSource = new PaymentSource();
            $paymentSource->setTitle($params['title']);
            $paymentSource->setPrivatePay((bool)$params['private_pay']);
            $paymentSource->setPeriod($period);
            $paymentSource->setCareLevelAdjustment($careLevelAdjustment);
            $paymentSource->setSpace($space);
            $paymentSource->setResidentName((bool)$params['resident_name']);
            $paymentSource->setDateOfBirth((bool)$params['date_of_birth']);
            $paymentSource->setFieldName($params['field_name']);
            $paymentSource->setFieldText($params['field_text']);
            $paymentSource->setOnlyForOccupiedDays((bool)$params['only_for_occupied_days']);

            $this->validate($paymentSource, null, ['api_admin_payment_source_add']);

            if ($careLevelAdjustment === true) {
                $paymentSource->setAmount(null);
            } else {
                $paymentSource->setAmount($params['amount']);

                $this->validate($paymentSource, null, ['api_admin_payment_source_amount_add']);
            }

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

            if ($careLevelAdjustment) {
                /** @var CareLevelRepository $careLevelRepo */
                $careLevelRepo = $this->em->getRepository(CareLevel::class);
                $careLevels = $careLevelRepo->list($currentSpace, null);
                $countCareLevels = count($careLevels);

                $paymentSources = $repo->findByIdsWithRates($currentSpace, null, [$entity->getId()]);
                /** @var PaymentSource $paymentSource */
                $paymentSource = $paymentSources[0];
                /** @var PaymentSourceBaseRate $baseRate */
                $baseRate = $paymentSource->getBaseRates()[0];
                $countLevels = count($baseRate->getLevels());

                if ($countLevels < $countCareLevels) {
                    $diff = $countCareLevels - $countLevels;
                    if ($diff > 1) {
                        $exceptionMessage = 'Missing Base Rate for ' . $diff . ' Care Levels.';
                    } else {
                        $exceptionMessage = 'Missing Base Rate for ' . $diff . ' Care Level.';
                    }

                    throw new MissingBaseRateForCareLevelException($exceptionMessage);
                }
            }

            $entity->setTitle($params['title']);
            $entity->setPrivatePay((bool)$params['private_pay']);
            $entity->setPeriod($period);
            $entity->setCareLevelAdjustment($careLevelAdjustment);
            $entity->setSpace($space);
            $entity->setResidentName((bool)$params['resident_name']);
            $entity->setDateOfBirth((bool)$params['date_of_birth']);
            $entity->setFieldName($params['field_name']);
            $entity->setFieldText($params['field_text']);
            $entity->setOnlyForOccupiedDays((bool)$params['only_for_occupied_days']);

            $this->validate($entity, null, ['api_admin_payment_source_edit']);

            if ($careLevelAdjustment === true) {
                $entity->setAmount(null);
            } else {
                $entity->setAmount($params['amount']);

                $this->validate($entity, null, ['api_admin_payment_source_amount_edit']);
            }

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

            /** @var ResidentLedgerRepository $ledgerRepo */
            $ledgerRepo = $this->em->getRepository(ResidentLedger::class);

            $ledgers = $ledgerRepo->getEntityWithSources($this->grantService->getCurrentSpace(), null);

            if (!empty($ledgers)) {
                /** @var ResidentLedger $ledger */
                foreach ($ledgers as $ledger) {
                    if (!empty($ledger->getSource())) {
                        $sources = $ledger->getSource();
                        foreach ($sources as $key => $source) {
                            if ($source['id'] === $id) {
                                unset($sources[$key]);
                                $changedSources = array_values($sources);
                                $ledger->setSource($changedSources);
                            }
                        }

                        $this->em->persist($ledger);
                    }

                    if (!empty($ledger->getPrivatPaySource())) {
                        $privatPaySources = $ledger->getPrivatPaySource();
                        foreach ($privatPaySources as $key => $source) {
                            if ($source['id'] === $id) {
                                unset($privatPaySources[$key]);
                                $changedSources = array_values($privatPaySources);
                                $ledger->setPrivatPaySource($changedSources);
                            }
                        }

                        $this->em->persist($ledger);
                    }

                    if (!empty($ledger->getNotPrivatPaySource())) {
                        $notPrivatPaySources = $ledger->getNotPrivatPaySource();
                        foreach ($notPrivatPaySources as $key => $source) {
                            if ($source['id'] === $id) {
                                unset($notPrivatPaySources[$key]);
                                $changedSources = array_values($notPrivatPaySources);
                                $ledger->setNotPrivatPaySource($changedSources);
                            }
                        }

                        $this->em->persist($ledger);
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

            /** @var ResidentLedgerRepository $ledgerRepo */
            $ledgerRepo = $this->em->getRepository(ResidentLedger::class);

            $ledgers = $ledgerRepo->getEntityWithSources($this->grantService->getCurrentSpace(), null);

            if (!empty($ledgers)) {
                foreach ($ids as $id) {
                    /** @var ResidentLedger $ledger */
                    foreach ($ledgers as $ledger) {
                        if (!empty($ledger->getSource())) {
                            $sources = $ledger->getSource();
                            foreach ($sources as $key => $source) {
                                if ($source['id'] === $id) {
                                    unset($sources[$key]);
                                    $changedSources = array_values($sources);
                                    $ledger->setSource($changedSources);
                                }
                            }

                            $this->em->persist($ledger);
                        }

                        if (!empty($ledger->getPrivatPaySource())) {
                            $privatPaySources = $ledger->getPrivatPaySource();
                            foreach ($privatPaySources as $key => $source) {
                                if ($source['id'] === $id) {
                                    unset($privatPaySources[$key]);
                                    $changedSources = array_values($privatPaySources);
                                    $ledger->setPrivatPaySource($changedSources);
                                }
                            }

                            $this->em->persist($ledger);
                        }

                        if (!empty($ledger->getNotPrivatPaySource())) {
                            $notPrivatPaySources = $ledger->getNotPrivatPaySource();
                            foreach ($notPrivatPaySources as $key => $source) {
                                if ($source['id'] === $id) {
                                    unset($notPrivatPaySources[$key]);
                                    $changedSources = array_values($notPrivatPaySources);
                                    $ledger->setNotPrivatPaySource($changedSources);
                                }
                            }

                            $this->em->persist($ledger);
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
