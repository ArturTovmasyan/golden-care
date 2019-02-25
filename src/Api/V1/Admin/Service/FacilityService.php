<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CityStateZip;
use App\Entity\Facility;
use App\Entity\Space;
use App\Repository\CityStateZipRepository;
use App\Repository\FacilityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityService
 * @package App\Api\V1\Admin\Service
 */
class FacilityService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var FacilityRepository $repo */
        $repo = $this->em->getRepository(Facility::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var FacilityRepository $repo */
        $repo = $this->em->getRepository(Facility::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class));
    }

    /**
     * @param $id
     * @return Facility|null|object
     */
    public function getById($id)
    {
        /** @var FacilityRepository $repo */
        $repo = $this->em->getRepository(Facility::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
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

            $facility = new Facility();
            $facility->setName($params['name']);
            $facility->setDescription($params['description']);
            $facility->setShorthand($params['shorthand']);
            $facility->setPhone($params['phone']);
            $facility->setFax($params['fax']);
            $facility->setAddress($params['address']);
            $facility->setLicense($params['license']);
            $facility->setCsz($csz);
            $facility->setLicenseCapacity((int)$params['license_capacity']);
            $facility->setCapacity((int)$params['capacity']);
            $facility->setSpace($space);

            $this->validate($facility, null, ['api_admin_facility_add']);

            $this->em->persist($facility);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Exception
     */
    public function edit($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var FacilityRepository $repo */
            $repo = $this->em->getRepository(Facility::class);

            /** @var Facility $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

            if ($entity === null) {
                throw new FacilityNotFoundException();
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
            $entity->setLicense($params['license']);
            $entity->setCsz($csz);
            $entity->setLicenseCapacity((int)$params['license_capacity']);
            $entity->setCapacity((int)$params['capacity']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_facility_edit']);

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

            /** @var FacilityRepository $repo */
            $repo = $this->em->getRepository(Facility::class);

            /** @var Facility $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

            if ($entity === null) {
                throw new FacilityNotFoundException();
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
                throw new FacilityNotFoundException();
            }

            /** @var FacilityRepository $repo */
            $repo = $this->em->getRepository(Facility::class);

            $facilities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $ids);

            if (empty($facilities)) {
                throw new FacilityNotFoundException();
            }

            /**
             * @var Facility $facility
             */
            foreach ($facilities as $facility) {
                $this->em->remove($facility);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
