<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\DuplicateSpecialityRequestException;
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
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Physician::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Physician::class)->list($this->grantService->getCurrentSpace());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->em->getRepository(Physician::class)->getOne($this->grantService->getCurrentSpace(), $id);
    }

    /**
     * @param array $params
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
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $space      = $this->em->getRepository(Space::class)->find($params['space_id']);

            $csz        = $this->em->getRepository(CityStateZip::class)->getOne($currentSpace, $params['csz_id']);
            $salutation = $this->em->getRepository(Salutation::class)->getOne($currentSpace, $params['salutation_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            if ($salutation === null) {
                throw new SalutationNotFoundException();
            }

            $specialityId  = $params['speciality_id'];
            $newSpeciality = $params['speciality'];
            $speciality    = null;

            if ((empty($specialityId) && empty($newSpeciality)) || (!empty($specialityId) && !empty($newSpeciality))) {
                throw new DuplicateSpecialityRequestException();
            }

            if (!empty($newSpeciality)) {
                $speciality = new Speciality();
                $speciality->setTitle($newSpeciality['title'] ?? '');
                $speciality->setSpace($space);

                $this->validate($speciality, null, ['api_admin_speciality_add']);
                $this->em->persist($speciality);
            } elseif (!empty($specialityId)) {
                $speciality = $this->em->getRepository(Speciality::class)->getOne($currentSpace, $specialityId);

                if ($speciality === null) {
                    throw new SpecialityNotFoundException();
                }
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
            $physician->setSpeciality($speciality);
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

            $space      = $this->em->getRepository(Space::class)->find($params['space_id']);
            $physician  = $this->em->getRepository(Physician::class)->getOne($currentSpace, $id);
            $csz        = $this->em->getRepository(CityStateZip::class)->getOne($currentSpace, $params['csz_id']);
            $salutation = $this->em->getRepository(Salutation::class)->getOne($currentSpace, $params['salutation_id']);

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

            $specialityId  = $params['speciality_id'];
            $newSpeciality = $params['speciality'];
            $speciality    = null;

            if ((empty($specialityId) && empty($newSpeciality)) || (!empty($specialityId) && !empty($newSpeciality))) {
                throw new DuplicateSpecialityRequestException();
            }

            if (!empty($newSpeciality)) {
                $speciality = new Speciality();
                $speciality->setTitle($newSpeciality['title'] ?? '');
                $speciality->setSpace($space);

                $this->validate($speciality, null, ['api_admin_speciality_add']);
                $this->em->persist($speciality);
            } elseif (!empty($specialityId)) {
                $speciality = $this->em->getRepository(Speciality::class)->getOne($currentSpace, $specialityId);

                if ($speciality === null) {
                    throw new SpecialityNotFoundException();
                }
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
            $physician->setCsz($csz);
            $physician->setSpace($space);
            $physician->setSpeciality($speciality);
            $physician->setSalutation($salutation);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id): void
    {
        try {
            /**
             * @var Physician $physician
             */
            $this->em->getConnection()->beginTransaction();

            $physician = $this->em->getRepository(Physician::class)->getOne($this->grantService->getCurrentSpace(), $id);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new PhysicianNotFoundException();
            }

            $physicians = $this->em->getRepository(Physician::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

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
}
