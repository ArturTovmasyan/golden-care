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
use App\Entity\Apartment;
use App\Entity\CityStateZip;
use App\Entity\Facility;
use App\Entity\Physician;
use App\Entity\Region;
use App\Entity\Salutation;
use App\Entity\Space;
use App\Entity\Speciality;
use App\Model\ContractType;
use App\Model\Report\Base;
use App\Model\Report\PhysicianFull;
use App\Model\Report\PhysicianSimple;
use App\Model\Resident;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * Class PhysicianService
 * @package App\Api\V1\Dashboard\Service
 */
class PhysicianService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $spaceId = false;

        if (!empty($params) && !empty($params[0]['space_id'])) {
            $spaceId = $params[0]['space_id'];
        }

        $this->em->getRepository(Physician::class)->search($queryBuilder, $spaceId);
    }

    /**
     * @param $params
     * @return array|object[]
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['space_id'])) {
            $spaceId = $params[0]['space_id'];

            return $this->em->getRepository(Physician::class)->findBy(['space' => $spaceId]);
        }

        return $this->em->getRepository(Physician::class)->findAll();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->em->getRepository(Physician::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function add(array $params): void
    {
        try {
            /**
             * @var Physician $physician
             * @var Salutation $salutation
             * @var CityStateZip $csz
             * @var Speciality $speciality
             */
            $this->em->getConnection()->beginTransaction();

            $space      = $this->em->getRepository(Space::class)->find($params['space_id']);
            $csz        = $this->em->getRepository(CityStateZip::class)->find($params['csz_id']);
            $salutation = $this->em->getRepository(Salutation::class)->find($params['salutation_id']);

            if (is_null($space)) {
                throw new SpaceNotFoundException();
            }

            if (is_null($csz)) {
                throw new CityStateZipNotFoundException();
            }

            if (is_null($salutation)) {
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

                $this->validate($speciality, null, ["api_admin_speciality_add"]);
                $this->em->persist($speciality);
            } elseif (!empty($specialityId)) {
                $speciality = $this->em->getRepository(Speciality::class)->find($specialityId);

                if (is_null($speciality)) {
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
            $physician->setSpace($space);
            $physician->setSpeciality($speciality);
            $physician->setSalutation($salutation);
            $physician->setSpeciality($speciality);

            $this->validate($physician, null, ["api_admin_physician_add"]);

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
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
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

            $space      = $this->em->getRepository(Space::class)->find($params['space_id']);
            $physician  = $this->em->getRepository(Physician::class)->find($id);
            $csz        = $this->em->getRepository(CityStateZip::class)->find($params['csz_id']);
            $salutation = $this->em->getRepository(Salutation::class)->find($params['salutation_id']);

            if (is_null($physician)) {
                throw new PhysicianNotFoundException();
            }

            if (is_null($space)) {
                throw new SpaceNotFoundException();
            }

            if (is_null($csz)) {
                throw new CityStateZipNotFoundException();
            }

            if (is_null($salutation)) {
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

                $this->validate($speciality, null, ["api_admin_speciality_add"]);
                $this->em->persist($speciality);
            } elseif (!empty($specialityId)) {
                $speciality = $this->em->getRepository(Speciality::class)->find($specialityId);

                if (is_null($speciality)) {
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

            $this->validate($physician, null, ["api_admin_physician_edit"]);

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

            $physician = $this->em->getRepository(Physician::class)->find($id);

            if (is_null($physician)) {
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
            if (empty($ids)) {
                throw new PhysicianNotFoundException();
            }

            $physicians = $this->em->getRepository(Physician::class)->findByIds($ids);

            if (empty($physicians)) {
                throw new PhysicianNotFoundException();
            }

            /**
             * @var Physician $physician
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($physicians as $physician) {
                $this->em->remove($physician);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (PhysicianNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param Request $request
     * @return PhysicianSimple
     */
    public function getSimpleReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id') ?? false;

        if (!$type) {
            throw new ParameterNotFoundException('type');
        }

        if ($type && !in_array($type, Resident::getTypeValues())) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $physicians = $this->em->getRepository(Physician::class)->getPhysicianSimpleReport($type, $typeId);

        $physiciansByTypeId = [];
        foreach ($physicians as $physician) {
            $physiciansByTypeId[$physician['typeId']][] = $physician;
        }

        // create report
        $report = new PhysicianSimple();
        $report->setTitle('PHYSICIAN, ROSTER SIMPLE');
        $report->setType($type);
        $report->setPhysicianData($physiciansByTypeId);

        return $report;
    }

    /**
     * @param Request $request
     * @return PhysicianFull
     */
    public function getFullReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id') ?? false;

        if (!$type) {
            throw new ParameterNotFoundException('type');
        }

        if ($type && !in_array($type, Resident::getTypeValues())) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        try {
            $physicians = $this->em->getRepository(Physician::class)->getPhysicianFullReport($type, $typeId);
        } catch (\Exception $e) {
            $physicians = [];
        }

        // create report
        $report = new PhysicianFull();
        $report->setTitle('PHYSICIAN ROSTER, FULL');
        $report->setType(ContractType::getTypes()[$type]);
        $report->setPhysicians($physicians);

        return $report;
    }
}
