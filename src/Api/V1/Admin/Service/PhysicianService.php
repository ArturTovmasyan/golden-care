<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Api\V1\Common\Service\Exception\SalutationNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\SpecialityNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CityStateZip;
use App\Entity\Physician;
use App\Entity\Salutation;
use App\Entity\Space;
use App\Entity\Speciality;
use App\Repository\CityStateZipRepository;
use App\Repository\PhysicianRepository;
use App\Repository\SalutationRepository;
use App\Repository\SpecialityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PhysicianService
 * @package App\Api\V1\Admin\Service
 */
class PhysicianService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var PhysicianRepository $repo */
        $repo = $this->em->getRepository(Physician::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Physician::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var PhysicianRepository $repo */
        $repo = $this->em->getRepository(Physician::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Physician::class));
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        /** @var PhysicianRepository $repo */
        $repo = $this->em->getRepository(Physician::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Physician::class), $id);
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
            /**
             * @var Physician $physician
             * @var Salutation $salutation
             * @var CityStateZip $csz
             * @var Speciality $speciality
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $space = $this->getSpace($params['space_id']);

            /** @var CityStateZipRepository $cszRepo */
            $cszRepo = $this->em->getRepository(CityStateZip::class);

            /** @var CityStateZip $csz */
            $csz = $cszRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $params['csz_id']);

            /** @var SalutationRepository $salutationRepo */
            $salutationRepo = $this->em->getRepository(Salutation::class);

            $salutation = $salutationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Salutation::class), $params['salutation_id']);

            /** @var SpecialityRepository $specialityRepo */
            $specialityRepo = $this->em->getRepository(Speciality::class);

            $speciality = $specialityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Speciality::class), $params['speciality_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            if ($salutation === null) {
                throw new SalutationNotFoundException();
            }

            if ($speciality === null) {
                throw new SpecialityNotFoundException();
            }

            // save physician
            $physician = new Physician();
            $physician->setFirstName($params['first_name'] ?? '');
            $physician->setMiddleName($params['middle_name'] ?? '');
            $physician->setLastName($params['last_name'] ?? '');
            $physician->setAddress1($params['address_1'] ?? '');
            $physician->setAddress2($params['address_2'] ?? '');
            $physician->setOfficePhone($params['office_phone'] ?? '');
            $physician->setFax($params['fax'] ?? '');
            $physician->setEmergencyPhone($params['emergency_phone'] ?? '');
            $physician->setEmail($params['email'] ?? '');
            $physician->setWebsiteUrl($params['website_url'] ?? '');
            $physician->setSpace($space);
            $physician->setCsz($csz);
            $physician->setSalutation($salutation);
            $physician->setSpeciality($speciality);

            $this->validate($physician, null, ['api_admin_physician_add']);

            $this->em->persist($physician);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $physician->getId();
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
            /**
             * @var Physician $physician
             * @var Salutation $salutation
             * @var CityStateZip $csz
             * @var Speciality $speciality
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $space = $this->getSpace($params['space_id']);

            /** @var PhysicianRepository $repo */
            $repo = $this->em->getRepository(Physician::class);

            $physician = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Physician::class), $id);

            /** @var CityStateZipRepository $cszRepo */
            $cszRepo = $this->em->getRepository(CityStateZip::class);

            /** @var CityStateZip $csz */
            $csz = $cszRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $params['csz_id']);

            /** @var SalutationRepository $salutationRepo */
            $salutationRepo = $this->em->getRepository(Salutation::class);

            $salutation = $salutationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Salutation::class), $params['salutation_id']);

            /** @var SpecialityRepository $specialityRepo */
            $specialityRepo = $this->em->getRepository(Speciality::class);

            $speciality = $specialityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Speciality::class), $params['speciality_id']);

            if ($physician === null) {
                throw new PhysicianNotFoundException();
            }

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            if ($salutation === null) {
                throw new SalutationNotFoundException();
            }

            if ($speciality === null) {
                throw new SpecialityNotFoundException();
            }

            // edit physician
            $physician->setFirstName($params['first_name'] ?? '');
            $physician->setMiddleName($params['middle_name'] ?? '');
            $physician->setLastName($params['last_name'] ?? '');
            $physician->setAddress1($params['address_1'] ?? '');
            $physician->setAddress2($params['address_2'] ?? '');
            $physician->setOfficePhone($params['office_phone'] ?? '');
            $physician->setFax($params['fax'] ?? '');
            $physician->setEmergencyPhone($params['emergency_phone'] ?? '');
            $physician->setEmail($params['email'] ?? '');
            $physician->setWebsiteUrl($params['website_url'] ?? '');
            $physician->setSpace($space);
            $physician->setCsz($csz);
            $physician->setSalutation($salutation);
            $physician->setSpeciality($speciality);

            $this->validate($physician, null, ['api_admin_physician_edit']);

            $this->em->persist($physician);
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
    public function remove($id): void
    {
        try {
            /**
             * @var Physician $physician
             */
            $this->em->getConnection()->beginTransaction();

            /** @var PhysicianRepository $repo */
            $repo = $this->em->getRepository(Physician::class);

            $physician = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Physician::class), $id);

            if ($physician === null) {
                throw new PhysicianNotFoundException();
            }

            $this->em->remove($physician);
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
                throw new PhysicianNotFoundException();
            }

            /** @var PhysicianRepository $repo */
            $repo = $this->em->getRepository(Physician::class);

            $physicians = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Physician::class), $ids);

            if (empty($physicians)) {
                throw new PhysicianNotFoundException();
            }

            /**
             * @var Physician $physician
             */
            foreach ($physicians as $physician) {
                $this->em->remove($physician);
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
            throw new PhysicianNotFoundException();
        }

        /** @var PhysicianRepository $repo */
        $repo = $this->em->getRepository(Physician::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Physician::class), $ids);

        if (empty($entities)) {
            throw new PhysicianNotFoundException();
        }

        return $this->getRelatedData(Physician::class, $entities);
    }
}
