<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\ApartmentNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ApartmentBed;
use App\Entity\CityStateZip;
use App\Entity\Apartment;
use App\Entity\Space;
use App\Repository\ApartmentBedRepository;
use App\Repository\ApartmentRepository;
use App\Repository\CityStateZipRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ApartmentService
 * @package App\Api\V1\Admin\Service
 */
class ApartmentService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var ApartmentRepository $repo */
        $repo = $this->em->getRepository(Apartment::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var ApartmentRepository $repo */
        $repo = $this->em->getRepository(Apartment::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Apartment::class));
    }

    /**
     * @param $id
     * @return Apartment|null|object
     */
    public function getById($id)
    {
        /** @var ApartmentRepository $repo */
        $repo = $this->em->getRepository(Apartment::class);

        $apartment = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $id);

        if ($apartment !== null) {
            /** @var ApartmentBedRepository $bedRepo */
            $bedRepo = $this->em->getRepository(ApartmentBed::class);

            $bedsConfigured = $bedRepo->getBedCount($apartment->getId());

            $apartment->setBedsConfigured($bedsConfigured);
        }

        return $apartment;
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

            $cszId = $params['csz_id'] ?? 0;

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            /** @var CityStateZipRepository $cszRepo */
            $cszRepo = $this->em->getRepository(CityStateZip::class);

            /** @var CityStateZip $csz */
            $csz = $cszRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $cszId);

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            $apartment = new Apartment();
            $apartment->setName($params['name']);
            $apartment->setDescription($params['description']);
            $apartment->setShorthand($params['shorthand']);
            $apartment->setPhone($params['phone']);
            $apartment->setFax($params['fax']);
            $apartment->setAddress($params['address']);
            $apartment->setCsz($csz);
            $apartment->setBedsLicensed((int)$params['beds_licensed']);
            $apartment->setBedsTarget((int)$params['beds_target']);
            $apartment->setSpace($space);

            $this->validate($apartment, null, ['api_admin_apartment_add']);

            $this->em->persist($apartment);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $apartment->getId();
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

            /** @var ApartmentRepository $repo */
            $repo = $this->em->getRepository(Apartment::class);

            /** @var Apartment $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Apartment::class), $id);

            if ($entity === null) {
                throw new ApartmentNotFoundException();
            }

            $cszId = $params['csz_id'] ?? 0;

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            /** @var CityStateZipRepository $cszRepo */
            $cszRepo = $this->em->getRepository(CityStateZip::class);

            /** @var CityStateZip $csz */
            $csz = $cszRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $cszId);

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            $entity->setName($params['name']);
            $entity->setDescription($params['description']);
            $entity->setShorthand($params['shorthand']);
            $entity->setPhone($params['phone']);
            $entity->setFax($params['fax']);
            $entity->setAddress($params['address']);
            $entity->setCsz($csz);
            $entity->setBedsLicensed((int)$params['beds_licensed']);
            $entity->setBedsTarget((int)$params['beds_target']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_apartment_edit']);

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

            /** @var ApartmentRepository $repo */
            $repo = $this->em->getRepository(Apartment::class);

            /** @var Apartment $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $id);

            if ($entity === null) {
                throw new ApartmentNotFoundException();
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
                throw new ApartmentNotFoundException();
            }

            /** @var ApartmentRepository $repo */
            $repo = $this->em->getRepository(Apartment::class);

            $apartments = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $ids);

            if (empty($apartments)) {
                throw new ApartmentNotFoundException();
            }

            /**
             * @var Apartment $apartment
             */
            foreach ($apartments as $apartment) {
                $this->em->remove($apartment);
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
            throw new ApartmentNotFoundException();
        }

        /** @var ApartmentRepository $repo */
        $repo = $this->em->getRepository(Apartment::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $ids);

        if (empty($entities)) {
            throw new ApartmentNotFoundException();
        }

        return $this->getRelatedData(Apartment::class, $entities);
    }

    /**
     * @param $date
     * @return mixed
     */
    public function getMobileList($date)
    {
        /** @var ApartmentRepository $repo */
        $repo = $this->em->getRepository(Apartment::class);

        $entities = $repo->mobileList($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $date);

        $finalEntities = [];
        if (!empty($entities)) {
            foreach ($entities as $entity) {
                $entity['updated_at'] = $entity['updated_at'] !== null ? $entity['updated_at']->format('Y-m-d H:i:s') : $entity['updated_at'];

                $finalEntities[] = $entity;
            }
        }

        return $finalEntities;
    }
}
