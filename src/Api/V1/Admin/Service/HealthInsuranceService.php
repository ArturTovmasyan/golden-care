<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\HealthInsuranceNotFoundException;
use App\Api\V1\Common\Service\Exception\InsuranceCompanyNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Api\V1\Common\Service\ImageFilterService;
use App\Entity\HealthInsuranceFile;
use App\Entity\InsuranceCompany;
use App\Entity\Resident;
use App\Entity\HealthInsurance;
use App\Repository\HealthInsuranceFileRepository;
use App\Repository\HealthInsuranceRepository;
use App\Repository\InsuranceCompanyRepository;
use App\Repository\ResidentRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class HealthInsuranceService
 * @package App\Api\V1\Admin\Service
 */
class HealthInsuranceService extends BaseService implements IGridService
{
    /**
     * @var ImageFilterService
     */
    private $imageFilterService;

    /**
     * @param ImageFilterService $imageFilterService
     */
    public function setImageFilterService(ImageFilterService $imageFilterService)
    {
        $this->imageFilterService = $imageFilterService;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('hi.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var HealthInsuranceRepository $repo */
        $repo = $this->em->getRepository(HealthInsurance::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(HealthInsurance::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var HealthInsuranceRepository $repo */
            $repo = $this->em->getRepository(HealthInsurance::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(HealthInsurance::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return HealthInsurance|null|object
     */
    public function getById($id)
    {
        /** @var HealthInsuranceRepository $repo */
        $repo = $this->em->getRepository(HealthInsurance::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(HealthInsurance::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Exception
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $params['resident_id']);

            /** @var InsuranceCompanyRepository $companyRepo */
            $companyRepo = $this->em->getRepository(InsuranceCompany::class);

            /** @var InsuranceCompany $company */
            $company = $companyRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(InsuranceCompany::class), $params['company_id']);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            if ($company === null) {
                throw new InsuranceCompanyNotFoundException();
            }

            $healthInsurance = new HealthInsurance();
            $healthInsurance->setResident($resident);
            $healthInsurance->setCompany($company);
            $healthInsurance->setMedicalRecordNumber($params['medical_record_number']);
            $healthInsurance->setGroupNumber($params['group_number']);
            $healthInsurance->setNotes($params['notes']);

            $this->validate($healthInsurance, null, ['api_admin_health_insurance_add']);

            $this->em->persist($healthInsurance);

            $firstFile = !empty($params['first_file']) ? $params['first_file'] : null;
            $secondFile = !empty($params['second_file']) ? $params['second_file'] : null;

            // save file
            if ($firstFile !== null || $secondFile !== null) {
                $file = new HealthInsuranceFile();

                $file->setInsurance($healthInsurance);
                $file->setFirstFile($firstFile);
                $file->setSecondFile($secondFile);

                $this->validate($file, null, ['api_admin_health_insurance_file_add']);

                if ($file) {
                    $this->imageFilterService->validateFile($file);
                }

                $this->em->persist($file);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $healthInsurance->getId();
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
    public function edit($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var HealthInsuranceRepository $repo */
            $repo = $this->em->getRepository(HealthInsurance::class);

            /** @var HealthInsurance $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(HealthInsurance::class), $id);

            if ($entity === null) {
                throw new HealthInsuranceNotFoundException();
            }

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $params['resident_id']);

            /** @var InsuranceCompanyRepository $companyRepo */
            $companyRepo = $this->em->getRepository(InsuranceCompany::class);

            /** @var InsuranceCompany $company */
            $company = $companyRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(InsuranceCompany::class), $params['company_id']);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            if ($company === null) {
                throw new InsuranceCompanyNotFoundException();
            }

            $entity->setResident($resident);
            $entity->setCompany($company);
            $entity->setMedicalRecordNumber($params['medical_record_number']);
            $entity->setGroupNumber($params['group_number']);
            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_admin_health_insurance_edit']);

            $this->em->persist($entity);

            $firstFile = !empty($params['first_file']) ? $params['first_file'] : null;
            $secondFile = !empty($params['second_file']) ? $params['second_file'] : null;

            // save file
            if ($firstFile !== null || $secondFile !== null) {
                /** @var HealthInsuranceFileRepository $fileRepo */
                $fileRepo = $this->em->getRepository(HealthInsuranceFile::class);

                $file = $fileRepo->getBy($entity->getId());

                if ($file === null) {
                    $file = new HealthInsuranceFile();
                }

                $file->setInsurance($entity);
                $file->setFirstFile($firstFile);
                $file->setSecondFile($secondFile);

                $this->validate($file, null, ['api_admin_health_insurance_file_edit']);

                if ($file) {
                    $this->imageFilterService->validateFile($file);
                }

                $this->em->persist($file);
            }

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

            /** @var HealthInsuranceRepository $repo */
            $repo = $this->em->getRepository(HealthInsurance::class);

            /** @var HealthInsurance $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(HealthInsurance::class), $id);

            if ($entity === null) {
                throw new HealthInsuranceNotFoundException();
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
                throw new HealthInsuranceNotFoundException();
            }

            /** @var HealthInsuranceRepository $repo */
            $repo = $this->em->getRepository(HealthInsurance::class);

            $healthInsurances = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(HealthInsurance::class), $ids);

            if (empty($healthInsurances)) {
                throw new HealthInsuranceNotFoundException();
            }

            /**
             * @var HealthInsurance $healthInsurance
             */
            foreach ($healthInsurances as $healthInsurance) {
                $this->em->remove($healthInsurance);
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
            throw new HealthInsuranceNotFoundException();
        }

        /** @var HealthInsuranceRepository $repo */
        $repo = $this->em->getRepository(HealthInsurance::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(HealthInsurance::class), $ids);

        if (empty($entities)) {
            throw new HealthInsuranceNotFoundException();
        }

        return $this->getRelatedData(HealthInsurance::class, $entities);
    }
}
