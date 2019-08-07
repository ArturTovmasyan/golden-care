<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\FileExtensionException;
use App\Api\V1\Common\Service\Exception\InsuranceCompanyNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentHealthInsuranceNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Api\V1\Common\Service\S3Service;
use App\Entity\File;
use App\Entity\InsuranceCompany;
use App\Entity\Resident;
use App\Entity\ResidentHealthInsurance;
use App\Model\FileType;
use App\Repository\FileRepository;
use App\Repository\InsuranceCompanyRepository;
use App\Repository\ResidentHealthInsuranceRepository;
use App\Repository\ResidentRepository;
use App\Util\MimeUtil;
use App\Util\StringUtil;
use DataURI\Parser;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentHealthInsuranceService
 * @package App\Api\V1\Admin\Service
 */
class ResidentHealthInsuranceService extends BaseService implements IGridService
{
    /**
     * @var S3Service
     */
    private $s3Service;

    /**
     * @param S3Service $s3Service
     */
    public function setS3Service(S3Service $s3Service)
    {
        $this->s3Service = $s3Service;
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

            $list = $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentHealthInsurance::class), $residentId);

            /** @var ResidentHealthInsurance $entity */
            foreach ($list as $entity) {
                if ($entity !== null && $entity->getFirstFile() !== null) {
                    $cmdFirst = $this->s3Service->getS3Client()->getCommand('GetObject', [
                        'Bucket' => getenv('AWS_BUCKET'),
                        'Key'    => $entity->getFirstFile()->getType() . '/' . $entity->getFirstFile()->getS3Id(),
                    ]);
                    $s3RequestFirst = $this->s3Service->getS3Client()->createPresignedRequest($cmdFirst, '+20 minutes');

                    $entity->setFirstFileDownloadUrl((string)$s3RequestFirst->getUri());
                } else {
                    $entity->setFirstFileDownloadUrl(null);
                }

                if ($entity !== null && $entity->getSecondFile() !== null) {
                    $cmdSecond = $this->s3Service->getS3Client()->getCommand('GetObject', [
                        'Bucket' => getenv('AWS_BUCKET'),
                        'Key'    => $entity->getSecondFile()->getType() . '/' . $entity->getSecondFile()->getS3Id(),
                    ]);
                    $s3RequestSecond = $this->s3Service->getS3Client()->createPresignedRequest($cmdSecond, '+20 minutes');

                    $entity->setSecondFileDownloadUrl((string)$s3RequestSecond->getUri());
                } else {
                    $entity->setSecondFileDownloadUrl(null);
                }
            }

            return $list;
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

            $fileFirst = !empty($params['first_file']) ? $params['first_file'] : null;
            $fileSecond = !empty($params['second_file']) ? $params['second_file'] : null;

            $filterService = $this->container->getParameter('filter_service');
            $pdfFileService = $this->container->getParameter('pdf_file_service');

            // save files
            if ($fileFirst !== null) {
                $firstFile = new File();

                $parseFirstFile = Parser::parse($fileFirst);
                $firstFile->setMimeType($parseFirstFile->getMimeType());
                $firstFile->setType(FileType::TYPE_RESIDENT_INSURANCE);

                $this->validate($firstFile, null, ['api_admin_file_add']);

                $this->em->persist($firstFile);

                //validate file
                if ($firstFile->getMimeType() === 'application/pdf' && !\in_array(MimeUtil::mime2ext($firstFile->getMimeType()), $pdfFileService['extensions'], false)) {
                    throw new FileExtensionException();
                }

                if ($firstFile->getMimeType() !== 'application/pdf' && !\in_array(MimeUtil::mime2ext($firstFile->getMimeType()), $filterService['extensions'], false)) {
                    throw new FileExtensionException();
                }

                $s3Id = $firstFile->getId().'.'.MimeUtil::mime2ext($firstFile->getMimeType());
                $firstFile->setS3Id($s3Id);
                $this->em->persist($firstFile);

                $this->s3Service->uploadFile($fileFirst, $s3Id, $firstFile->getType(), $firstFile->getMimeType());

                $residentHealthInsurance->setFirstFile($firstFile);
            } else {
                $residentHealthInsurance->setFirstFile(null);
            }

            if ($fileSecond !== null) {
                $secondFile = new File();

                $parseSecondFile = Parser::parse($fileSecond);
                $secondFile->setMimeType($parseSecondFile->getMimeType());
                $secondFile->setType(FileType::TYPE_RESIDENT_INSURANCE);

                $this->validate($secondFile, null, ['api_admin_file_add']);

                $this->em->persist($secondFile);

                //validate file
                if ($secondFile->getMimeType() === 'application/pdf' && !\in_array(MimeUtil::mime2ext($secondFile->getMimeType()), $pdfFileService['extensions'], false)) {
                    throw new FileExtensionException();
                }

                if ($secondFile->getMimeType() !== 'application/pdf' && !\in_array(MimeUtil::mime2ext($secondFile->getMimeType()), $filterService['extensions'], false)) {
                    throw new FileExtensionException();
                }

                $s3Id = $secondFile->getId().'.'.MimeUtil::mime2ext($secondFile->getMimeType());
                $secondFile->setS3Id($s3Id);
                $this->em->persist($secondFile);

                $this->s3Service->uploadFile($fileSecond, $s3Id, $secondFile->getType(), $secondFile->getMimeType());

                $residentHealthInsurance->setSecondFile($secondFile);
            } else {
                $residentHealthInsurance->setSecondFile(null);
            }

            $this->em->persist($residentHealthInsurance);

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

            $fileFirst = !empty($params['first_file']) ? $params['first_file'] : null;
            $fileSecond = !empty($params['second_file']) ? $params['second_file'] : null;

            $filterService = $this->container->getParameter('filter_service');
            $pdfFileService = $this->container->getParameter('pdf_file_service');

            $firstFile = $entity->getFirstFile();
            if (!empty($fileFirst !== null)) {
                if (!StringUtil::starts_with($fileFirst, 'http')) {
                    if ($firstFile !== null) {
                        $this->s3Service->removeFile($firstFile->getS3Id(), $firstFile->getType());
                    } else {
                        $firstFile = new File();
                    }

                    $parseFile = Parser::parse($fileFirst);

                    $firstFile->setMimeType($parseFile->getMimeType());
                    $firstFile->setType(FileType::TYPE_RESIDENT_INSURANCE);

                    $this->validate($firstFile, null, ['api_admin_file_edit']);

                    $this->em->persist($firstFile);

                    //validate file
                    if ($firstFile->getMimeType() === 'application/pdf' && !\in_array(MimeUtil::mime2ext($firstFile->getMimeType()), $pdfFileService['extensions'], false)) {
                        throw new FileExtensionException();
                    }

                    if ($firstFile->getMimeType() !== 'application/pdf' && !\in_array(MimeUtil::mime2ext($firstFile->getMimeType()), $filterService['extensions'], false)) {
                        throw new FileExtensionException();
                    }

                    $s3Id = $firstFile->getId().'.'.MimeUtil::mime2ext($firstFile->getMimeType());
                    $firstFile->setS3Id($s3Id);
                    $this->em->persist($firstFile);

                    $this->s3Service->uploadFile($fileFirst, $s3Id, $firstFile->getType(), $firstFile->getMimeType());

                    $entity->setFirstFile($firstFile);
                }
            } else {
                if ($firstFile !== null) {
                    $this->s3Service->removeFile($firstFile->getS3Id(), $firstFile->getType());
                    $this->em->remove($firstFile);
                }

                $entity->setFirstFile(null);
            }

            $secondFile = $entity->getSecondFile();
            if (!empty($fileSecond !== null)) {
                if (!StringUtil::starts_with($fileSecond, 'http')) {
                    if ($secondFile !== null) {
                        $this->s3Service->removeFile($secondFile->getS3Id(), $secondFile->getType());
                    } else {
                        $secondFile = new File();
                    }

                    $parseFile = Parser::parse($fileSecond);

                    $secondFile->setMimeType($parseFile->getMimeType());
                    $secondFile->setType(FileType::TYPE_RESIDENT_INSURANCE);

                    $this->validate($secondFile, null, ['api_admin_file_edit']);

                    $this->em->persist($secondFile);

                    //validate file
                    if ($secondFile->getMimeType() === 'application/pdf' && !\in_array(MimeUtil::mime2ext($secondFile->getMimeType()), $pdfFileService['extensions'], false)) {
                        throw new FileExtensionException();
                    }

                    if ($secondFile->getMimeType() !== 'application/pdf' && !\in_array(MimeUtil::mime2ext($secondFile->getMimeType()), $filterService['extensions'], false)) {
                        throw new FileExtensionException();
                    }

                    $s3Id = $secondFile->getId().'.'.MimeUtil::mime2ext($secondFile->getMimeType());
                    $secondFile->setS3Id($s3Id);
                    $this->em->persist($secondFile);

                    $this->s3Service->uploadFile($fileSecond, $s3Id, $secondFile->getType(), $secondFile->getMimeType());

                    $entity->setSecondFile($secondFile);
                }
            } else {
                if ($secondFile !== null) {
                    $this->s3Service->removeFile($secondFile->getS3Id(), $secondFile->getType());
                    $this->em->remove($secondFile);
                }

                $entity->setSecondFile(null);
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

            /** @var ResidentHealthInsuranceRepository $repo */
            $repo = $this->em->getRepository(ResidentHealthInsurance::class);

            /** @var ResidentHealthInsurance $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentHealthInsurance::class), $id);

            if ($entity === null) {
                throw new ResidentHealthInsuranceNotFoundException();
            }

            $firstFile = $entity->getFirstFile();

            if ($firstFile !== null) {
                $this->s3Service->removeFile($firstFile->getS3Id(), $firstFile->getType());

                $this->em->remove($firstFile);
            }

            $secondFile = $entity->getSecondFile();

            if ($secondFile !== null) {
                $this->s3Service->removeFile($secondFile->getS3Id(), $secondFile->getType());

                $this->em->remove($secondFile);
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

            $firstFileIds = [];
            $secondFileIds = [];

            /** @var FileRepository $fileRepo */
            $fileRepo = $this->em->getRepository(File::class);

            /**
             * @var ResidentHealthInsurance $residentHealthInsurance
             */
            foreach ($residentHealthInsurances as $residentHealthInsurance) {
                if ($residentHealthInsurance->getFirstFile() !== null) {
                    $firstFileIds[] = $residentHealthInsurance->getFirstFile()->getId();
                }

                if ($residentHealthInsurance->getSecondFile() !== null) {
                    $secondFileIds[] = $residentHealthInsurance->getSecondFile()->getId();
                }

                $this->em->remove($residentHealthInsurance);
            }

            $firstFileIds = array_unique($firstFileIds);
            $secondFileIds = array_unique($secondFileIds);
            $fileIds = array_merge($firstFileIds, $secondFileIds);

            $files = $fileRepo->findByIds($fileIds);

            /**
             * @var File $file
             */
            foreach ($files as $file) {
                $this->s3Service->removeFile($file->getS3Id(), $file->getType());

                $this->em->remove($file);
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

    /**
     * @param $id
     * @param $number
     * @return array
     */
    public function downloadFile($id, $number): array
    {
        $entity = $this->getById($id);

        if ($number === 1 && !empty($entity) && $entity->getFirstFile() !== null) {
            return [$entity->getTitle(), $entity->getFirstFile()->getMimeType(), $this->s3Service->downloadFile($entity->getFirstFile()->getS3Id(), $entity->getFirstFile()->getType())];
        }

        if ($number === 2 && !empty($entity) && $entity->getSecondFile() !== null) {
            return [$entity->getTitle(), $entity->getSecondFile()->getMimeType(), $this->s3Service->downloadFile($entity->getSecondFile()->getS3Id(), $entity->getSecondFile()->getType())];
        }

        return [null, null, null];
    }
}
