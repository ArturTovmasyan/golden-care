<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\FileExtensionNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentDocumentNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\File;
use App\Entity\Resident;
use App\Entity\ResidentDocument;
use App\Model\FileType;
use App\Repository\FileRepository;
use App\Repository\ResidentDocumentRepository;
use App\Repository\ResidentRepository;
use App\Util\MimeUtil;
use App\Util\StringUtil;
use DataURI\Parser;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentDocumentService
 * @package App\Api\V1\Admin\Service
 */
class ResidentDocumentService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rd.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentDocumentRepository $repo */
        $repo = $this->em->getRepository(ResidentDocument::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDocument::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentDocumentRepository $repo */
            $repo = $this->em->getRepository(ResidentDocument::class);

            $list = $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDocument::class), $residentId);

            /** @var ResidentDocument $entity */
            foreach ($list as $entity) {
                if ($entity !== null && $entity->getFile() !== null) {
                    $uri = $this->s3Service->getFile($entity->getFile()->getS3Id(), $entity->getFile()->getType());

                    $entity->setDownloadUrl($uri);
                } else {
                    $entity->setDownloadUrl(null);
                }
            }

            return $list;
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentDocument|null|object
     */
    public function getById($id)
    {
        /** @var ResidentDocumentRepository $repo */
        $repo = $this->em->getRepository(ResidentDocument::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDocument::class), $id);
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
            $this->em->getConnection()->beginTransaction();

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $params['resident_id']);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $residentDocument = new ResidentDocument();
            $residentDocument->setResident($resident);
            $residentDocument->setTitle($params['title']);

            //save file
            $file = new File();

            if (!empty($params['file'])) {
                $fileData = explode(';', $params['file'], 2);

                $extensionData = explode('extension:', $fileData[0]);
                $extension = array_key_exists(1, $extensionData) ? $extensionData[1] : '';

                if (empty($extension)) {
                    throw new FileExtensionNotFoundException();
                }

                $base64 = $fileData[1];

                $parseFile = Parser::parse($base64);
                $file->setMimeType($parseFile->getMimeType());
                $file->setType(FileType::TYPE_RESIDENT_DOCUMENT);
                $file->setExtension($extension);

                $this->validate($file, null, ['api_admin_file_add']);

                $this->em->persist($file);

                $s3Id = $file->getId() . '.' . $extension;
                $file->setS3Id($s3Id);
                $this->em->persist($file);

                $residentDocument->setFile($file);
                $this->validate($residentDocument, null, ['api_admin_resident_document_add']);

                $this->s3Service->uploadFile($base64, $s3Id, $file->getType(), $file->getMimeType());
            } else {
                $residentDocument->setFile(null);
            }

            $this->validate($residentDocument, null, ['api_admin_resident_document_add']);

            $this->em->persist($residentDocument);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentDocument->getId();
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

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentDocumentRepository $repo */
            $repo = $this->em->getRepository(ResidentDocument::class);

            /** @var ResidentDocument $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDocument::class), $id);

            if ($entity === null) {
                throw new ResidentDocumentNotFoundException();
            }

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $params['resident_id']);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $entity->setResident($resident);
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

                    $fileData = explode(';', $params['file'], 2);

                    $extensionData = explode('extension:', $fileData[0]);
                    $extension = array_key_exists(1, $extensionData) ? $extensionData[1] : '';

                    if (empty($extension)) {
                        throw new FileExtensionNotFoundException();
                    }

                    $base64 = $fileData[1];

                    $parseFile = Parser::parse($base64);

                    $file->setMimeType($parseFile->getMimeType());
                    $file->setType(FileType::TYPE_RESIDENT_DOCUMENT);
                    $file->setExtension($extension);

                    $this->validate($file, null, ['api_admin_file_edit']);

                    $this->em->persist($file);

                    $s3Id = $file->getId() . '.' . $extension;
                    $file->setS3Id($s3Id);
                    $this->em->persist($file);

                    $entity->setFile($file);
                    $this->validate($entity, null, ['api_admin_resident_document_edit']);

                    $this->s3Service->uploadFile($base64, $s3Id, $file->getType(), $file->getMimeType());
                }
            } else {
                $entity->setFile(null);
            }

            $this->validate($entity, null, ['api_admin_resident_document_edit']);

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

            /** @var ResidentDocumentRepository $repo */
            $repo = $this->em->getRepository(ResidentDocument::class);

            /** @var ResidentDocument $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDocument::class), $id);

            if ($entity === null) {
                throw new ResidentDocumentNotFoundException();
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
                throw new ResidentDocumentNotFoundException();
            }

            /** @var ResidentDocumentRepository $repo */
            $repo = $this->em->getRepository(ResidentDocument::class);

            $residentDocuments = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDocument::class), $ids);

            if (empty($residentDocuments)) {
                throw new ResidentDocumentNotFoundException();
            }

            $fileIds = [];

            /** @var FileRepository $fileRepo */
            $fileRepo = $this->em->getRepository(File::class);

            /**
             * @var ResidentDocument $residentDocument
             */
            foreach ($residentDocuments as $residentDocument) {
                if ($residentDocument->getFile() !== null) {
                    $fileIds[] = $residentDocument->getFile()->getId();
                }

                $this->em->remove($residentDocument);
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
            throw new ResidentDocumentNotFoundException();
        }

        /** @var ResidentDocumentRepository $repo */
        $repo = $this->em->getRepository(ResidentDocument::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDocument::class), $ids);

        if (empty($entities)) {
            throw new ResidentDocumentNotFoundException();
        }

        return $this->getRelatedData(ResidentDocument::class, $entities);
    }

    /**
     * @param $id
     * @return array
     */
    public function downloadFile($id): array
    {
        $entity = $this->getById($id);

        if (!empty($entity) && $entity->getFile() !== null) {
            return [$entity->getTitle(), $entity->getFile()->getMimeType(), $this->s3Service->downloadFile($entity->getFile()->getS3Id(), $entity->getFile()->getType())];
        }

        return [null, null, null];
    }
}
