<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\FacilityDocumentNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\File;
use App\Entity\Facility;
use App\Entity\FacilityDocument;
use App\Model\FileType;
use App\Repository\FileRepository;
use App\Repository\FacilityDocumentRepository;
use App\Repository\FacilityRepository;
use App\Util\MimeUtil;
use App\Util\StringUtil;
use DataURI\Parser;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityDocumentService
 * @package App\Api\V1\Admin\Service
 */
class FacilityDocumentService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        if (empty($params) || empty($params[0]['facility_id'])) {
            throw new FacilityNotFoundException();
        }

        $facilityId = $params[0]['facility_id'];

        $queryBuilder
            ->where('fd.facility = :facilityId')
            ->setParameter('facilityId', $facilityId);

        /** @var FacilityDocumentRepository $repo */
        $repo = $this->em->getRepository(FacilityDocument::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityDocument::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['facility_id'])) {
            $facilityId = $params[0]['facility_id'];

            /** @var FacilityDocumentRepository $repo */
            $repo = $this->em->getRepository(FacilityDocument::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityDocument::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);
        }

        throw new FacilityNotFoundException();
    }

    /**
     * @param $id
     * @return FacilityDocument|null|object
     */
    public function getById($id)
    {
        /** @var FacilityDocumentRepository $repo */
        $repo = $this->em->getRepository(FacilityDocument::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityDocument::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            /** @var Facility $facility */
            $facility = $facilityRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $params['facility_id']);

            if ($facility === null) {
                throw new FacilityNotFoundException();
            }

            $facilityDocument = new FacilityDocument();
            $facilityDocument->setFacility($facility);
            $facilityDocument->setTitle($params['title']);

            //save file
            $file = new File();

            if (!empty($params['file'])) {
                $parseFile = Parser::parse($params['file']);
                $file->setMimeType($parseFile->getMimeType());
                $file->setType(FileType::TYPE_FACILITY_DOCUMENT);

                $this->validate($file, null, ['api_admin_file_add']);

                $this->em->persist($file);

                $s3Id = $file->getId().'.'.MimeUtil::mime2ext($file->getMimeType());
                $file->setS3Id($s3Id);
                $this->em->persist($file);

                $facilityDocument->setFile($file);
                $this->validate($facilityDocument, null, ['api_admin_facility_document_add']);

                $this->s3Service->uploadFile($params['file'], $s3Id, $file->getType(), $file->getMimeType());
            } else {
                $facilityDocument->setFile(null);
            }

            $this->validate($facilityDocument, null, ['api_admin_facility_document_add']);

            $this->em->persist($facilityDocument);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $facilityDocument->getId();
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
    public function edit($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var FacilityDocumentRepository $repo */
            $repo = $this->em->getRepository(FacilityDocument::class);

            /** @var FacilityDocument $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityDocument::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

            if ($entity === null) {
                throw new FacilityDocumentNotFoundException();
            }

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            /** @var Facility $facility */
            $facility = $facilityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $params['facility_id']);

            if ($facility === null) {
                throw new FacilityNotFoundException();
            }

            $entity->setFacility($facility);
            $entity->setTitle($params['title']);

            // save file
            $file = $entity->getFile();

            if (!empty($params['file'])) {
                if (!StringUtil::starts_with($params['file'], 'http')) {
                    if ($file !== null) {
                        $this->s3Service->removeFile($file->getS3Id(), $file->getType());
                    } else {
                        $file = new File();
                    }

                    $parseFile = Parser::parse($params['file']);

                    $file->setMimeType($parseFile->getMimeType());
                    $file->setType(FileType::TYPE_FACILITY_DOCUMENT);

                    $this->validate($file, null, ['api_admin_file_edit']);

                    $this->em->persist($file);

                    $s3Id = $file->getId().'.'.MimeUtil::mime2ext($file->getMimeType());
                    $file->setS3Id($s3Id);
                    $this->em->persist($file);

                    $entity->setFile($file);
                    $this->validate($entity, null, ['api_admin_facility_document_edit']);

                    $this->s3Service->uploadFile($params['file'], $s3Id, $file->getType(), $file->getMimeType());
                }
            } else {
                $entity->setFile(null);
            }

            $this->validate($entity, null, ['api_admin_facility_document_edit']);

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

            /** @var FacilityDocumentRepository $repo */
            $repo = $this->em->getRepository(FacilityDocument::class);

            /** @var FacilityDocument $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityDocument::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

            if ($entity === null) {
                throw new FacilityDocumentNotFoundException();
            }

            $file = $entity->getFile();

            if ($file !== null) {
                $this->s3Service->removeFile($file->getS3Id(), $file->getType());

                $this->em->remove($file);
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
                throw new FacilityDocumentNotFoundException();
            }

            /** @var FacilityDocumentRepository $repo */
            $repo = $this->em->getRepository(FacilityDocument::class);

            $facilityDocuments = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityDocument::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $ids);

            if (empty($facilityDocuments)) {
                throw new FacilityDocumentNotFoundException();
            }

            $fileIds = [];

            /** @var FileRepository $fileRepo */
            $fileRepo = $this->em->getRepository(File::class);

            /**
             * @var FacilityDocument $facilityDocument
             */
            foreach ($facilityDocuments as $facilityDocument) {
                if ($facilityDocument->getFile() !== null) {
                    $fileIds[] = $facilityDocument->getFile()->getId();
                }

                $this->em->remove($facilityDocument);
            }

            $fileIds = array_unique($fileIds);

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
            throw new FacilityDocumentNotFoundException();
        }

        /** @var FacilityDocumentRepository $repo */
        $repo = $this->em->getRepository(FacilityDocument::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityDocument::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $ids);

        if (empty($entities)) {
            throw new FacilityDocumentNotFoundException();
        }

        return $this->getRelatedData(FacilityDocument::class, $entities);
    }

    /**
     * @param $id
     * @return array
     */
    public function downloadFile($id): array
    {
        $entity = $this->getById($id);

        if(!empty($entity) && $entity->getFile() !== null) {
            return [$entity->getTitle(), $entity->getFile()->getMimeType(), $this->s3Service->downloadFile($entity->getFile()->getS3Id(), $entity->getFile()->getType())];
        }

        return [null, null, null];
    }
}
