<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ResidentHealthInsuranceNotFoundException;
use App\Api\V1\Common\Service\Exception\InsuranceCompanyNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Api\V1\Common\Service\ImageFilterService;
use App\Entity\ResidentHealthInsuranceFile;
use App\Entity\InsuranceCompany;
use App\Entity\Resident;
use App\Entity\ResidentHealthInsurance;
use App\Repository\ResidentHealthInsuranceFileRepository;
use App\Repository\ResidentHealthInsuranceRepository;
use App\Repository\InsuranceCompanyRepository;
use App\Repository\ResidentRepository;
use DataURI\Parser;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentHealthInsuranceService
 * @package App\Api\V1\Admin\Service
 */
class ResidentHealthInsuranceService extends BaseService implements IGridService
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
            ->where('rhi.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentHealthInsuranceRepository $repo */
        $repo = $this->em->getRepository(ResidentHealthInsurance::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentHealthInsurance::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentHealthInsuranceRepository $repo */
            $repo = $this->em->getRepository(ResidentHealthInsurance::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentHealthInsurance::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentHealthInsurance|null|object
     */
    public function getById($id)
    {
        /** @var ResidentHealthInsuranceRepository $repo */
        $repo = $this->em->getRepository(ResidentHealthInsurance::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentHealthInsurance::class), $id);
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

            $residentHealthInsurance = new ResidentHealthInsurance();
            $residentHealthInsurance->setResident($resident);
            $residentHealthInsurance->setCompany($company);
            $residentHealthInsurance->setMedicalRecordNumber($params['medical_record_number']);
            $residentHealthInsurance->setGroupNumber($params['group_number']);
            $residentHealthInsurance->setNotes($params['notes']);

            $this->validate($residentHealthInsurance, null, ['api_admin_resident_health_insurance_add']);

            $this->em->persist($residentHealthInsurance);

            $firstFile = !empty($params['first_file']) ? $params['first_file'] : null;
            $secondFile = !empty($params['second_file']) ? $params['second_file'] : null;

            // save file
            if ($firstFile !== null || $secondFile !== null) {
                $file = new ResidentHealthInsuranceFile();

                $firstFile = Parser::parse($firstFile);
                $secondFile = Parser::parse($secondFile);

                $file->setInsurance($residentHealthInsurance);
                $file->setFirstFile($firstFile->getData());
                $file->setSecondFile($secondFile->getData());

                $this->validate($file, null, ['api_admin_resident_health_insurance_file_add']);

//                if ($file) {
//                    $this->imageFilterService->validateResidentHealthInsuranceFile($file);
//                }

                $this->em->persist($file);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentHealthInsurance->getId();
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

            /** @var ResidentHealthInsuranceRepository $repo */
            $repo = $this->em->getRepository(ResidentHealthInsurance::class);

            /** @var ResidentHealthInsurance $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentHealthInsurance::class), $id);

            if ($entity === null) {
                throw new ResidentHealthInsuranceNotFoundException();
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

            $this->validate($entity, null, ['api_admin_resident_health_insurance_edit']);

            $this->em->persist($entity);

            $firstFile = !empty($params['first_file']) ? $params['first_file'] : null;
            $secondFile = !empty($params['second_file']) ? $params['second_file'] : null;

            // save file
            if ($firstFile !== null || $secondFile !== null) {
                /** @var ResidentHealthInsuranceFileRepository $fileRepo */
                $fileRepo = $this->em->getRepository(ResidentHealthInsuranceFile::class);

                $file = $fileRepo->getBy($entity->getId());

                if ($file === null) {
                    $file = new ResidentHealthInsuranceFile();
                }

                $firstFile = Parser::parse($firstFile);
                $secondFile = Parser::parse($secondFile);

                $file->setInsurance($entity);
                $file->setFirstFile($firstFile->getData());
                $file->setSecondFile($secondFile->getData());

                $this->validate($file, null, ['api_admin_resident_health_insurance_file_edit']);

//                if ($file) {
//                    $this->imageFilterService->validateResidentHealthInsuranceFile($file);
//                }

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

            /** @var ResidentHealthInsuranceRepository $repo */
            $repo = $this->em->getRepository(ResidentHealthInsurance::class);

            /** @var ResidentHealthInsurance $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentHealthInsurance::class), $id);

            if ($entity === null) {
                throw new ResidentHealthInsuranceNotFoundException();
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
                throw new ResidentHealthInsuranceNotFoundException();
            }

            /** @var ResidentHealthInsuranceRepository $repo */
            $repo = $this->em->getRepository(ResidentHealthInsurance::class);

            $residentHealthInsurances = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentHealthInsurance::class), $ids);

            if (empty($residentHealthInsurances)) {
                throw new ResidentHealthInsuranceNotFoundException();
            }

            /**
             * @var ResidentHealthInsurance $residentHealthInsurance
             */
            foreach ($residentHealthInsurances as $residentHealthInsurance) {
                $this->em->remove($residentHealthInsurance);
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
            throw new ResidentHealthInsuranceNotFoundException();
        }

        /** @var ResidentHealthInsuranceRepository $repo */
        $repo = $this->em->getRepository(ResidentHealthInsurance::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentHealthInsurance::class), $ids);

        if (empty($entities)) {
            throw new ResidentHealthInsuranceNotFoundException();
        }

        return $this->getRelatedData(ResidentHealthInsurance::class, $entities);
    }
}
