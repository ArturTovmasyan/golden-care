<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\HospiceProviderNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CityStateZip;
use App\Entity\HospiceProvider;
use App\Entity\Salutation;
use App\Entity\Space;
use App\Repository\CityStateZipRepository;
use App\Repository\HospiceProviderRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class HospiceProviderService
 * @package App\Api\V1\Admin\Service
 */
class HospiceProviderService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var HospiceProviderRepository $repo */
        $repo = $this->em->getRepository(HospiceProvider::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(HospiceProvider::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var HospiceProviderRepository $repo */
        $repo = $this->em->getRepository(HospiceProvider::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(HospiceProvider::class));
    }

    /**
     * @param $id
     * @return HospiceProvider|null|object
     */
    public function getById($id)
    {
        /** @var HospiceProviderRepository $repo */
        $repo = $this->em->getRepository(HospiceProvider::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(HospiceProvider::class), $id);
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
            /**
             * @var Space $space
             * @var CityStateZip $csz
             * @var Salutation $salutation
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $hospiceProvider = new HospiceProvider();
            $hospiceProvider->setSpace($space);

            if (!empty($params['csz_id'])) {
                /** @var CityStateZipRepository $cszRepo */
                $cszRepo = $this->em->getRepository(CityStateZip::class);

                /** @var CityStateZip $csz */
                $csz = $cszRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $params['csz_id']);

                if ($csz === null) {
                    throw new CityStateZipNotFoundException();
                }

                $hospiceProvider->setCsz($csz);
            } else {
                $hospiceProvider->setCsz(null);
            }

            $hospiceProvider->setName($params['name'] ?? '');
            $hospiceProvider->setAddress1($params['address_1'] ?? '');
            $hospiceProvider->setAddress2($params['address_2'] ?? '');
            $hospiceProvider->setPhone($params['phone']);
            $hospiceProvider->setEmail($params['email'] ?? '');

            $this->validate($hospiceProvider, null, ['api_admin_hospice_provider_add']);
            $this->em->persist($hospiceProvider);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $hospiceProvider->getId();
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
            /**
             * @var Space $space
             * @var CityStateZip $csz
             * @var HospiceProvider $hospiceProvider
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

           /** @var HospiceProviderRepository $repo */
            $repo = $this->em->getRepository(HospiceProvider::class);

            $hospiceProvider = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(HospiceProvider::class), $id);

            if ($hospiceProvider === null) {
                throw new HospiceProviderNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $hospiceProvider->setSpace($space);

            if (!empty($params['csz_id'])) {
                /** @var CityStateZipRepository $cszRepo */
                $cszRepo = $this->em->getRepository(CityStateZip::class);

                /** @var CityStateZip $csz */
                $csz = $cszRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $params['csz_id']);

                if ($csz === null) {
                    throw new CityStateZipNotFoundException();
                }

                $hospiceProvider->setCsz($csz);
            } else {
                $hospiceProvider->setCsz(null);
            }

            $hospiceProvider->setName($params['name'] ?? '');
            $hospiceProvider->setAddress1($params['address_1'] ?? '');
            $hospiceProvider->setAddress2($params['address_2'] ?? '');
            $hospiceProvider->setPhone($params['phone']);
            $hospiceProvider->setEmail($params['email'] ?? '');

            $this->validate($hospiceProvider, null, ['api_admin_hospice_provider_edit']);
            $this->em->persist($hospiceProvider);

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

            /** @var HospiceProviderRepository $repo */
            $repo = $this->em->getRepository(HospiceProvider::class);

            /** @var HospiceProvider $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(HospiceProvider::class), $id);

            if ($entity === null) {
                throw new HospiceProviderNotFoundException();
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
                throw new HospiceProviderNotFoundException();
            }

            /** @var HospiceProviderRepository $repo */
            $repo = $this->em->getRepository(HospiceProvider::class);

            $hospiceProviders = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(HospiceProvider::class), $ids);

            if (empty($hospiceProviders)) {
                throw new HospiceProviderNotFoundException();
            }

            /**
             * @var HospiceProvider $hospiceProvider
             */
            foreach ($hospiceProviders as $hospiceProvider) {
                $this->em->remove($hospiceProvider);
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
            throw new HospiceProviderNotFoundException();
        }

        /** @var HospiceProviderRepository $repo */
        $repo = $this->em->getRepository(HospiceProvider::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(HospiceProvider::class), $ids);

        if (empty($entities)) {
            throw new HospiceProviderNotFoundException();
        }

        return $this->getRelatedData(HospiceProvider::class, $entities);
    }
}
