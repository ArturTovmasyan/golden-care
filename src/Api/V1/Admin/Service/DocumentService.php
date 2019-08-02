<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DocumentNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Api\V1\Common\Service\S3Service;
use App\Entity\Document;
use App\Entity\Facility;
use App\Entity\File;
use App\Entity\Space;
use App\Model\FileType;
use App\Repository\DocumentRepository;
use App\Repository\FacilityRepository;
use App\Repository\FileRepository;
use DataURI\Parser;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DocumentService
 * @package App\Api\V1\Admin\Service
 */
class DocumentService extends BaseService implements IGridService
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
        /** @var DocumentRepository $repo */
        $repo = $this->em->getRepository(Document::class);

        $facilityEntityGrants = !empty($this->grantService->getCurrentUserEntityGrants(Facility::class)) ? $this->grantService->getCurrentUserEntityGrants(Facility::class) : null;

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Document::class), $facilityEntityGrants, $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var DocumentRepository $repo */
        $repo = $this->em->getRepository(Document::class);

        $facilityEntityGrants = !empty($this->grantService->getCurrentUserEntityGrants(Facility::class)) ? $this->grantService->getCurrentUserEntityGrants(Facility::class) : null;

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Document::class), $facilityEntityGrants);
    }

    /**
     * @param $id
     * @return Document|null|object
     */
    public function getById($id)
    {
        /** @var DocumentRepository $repo */
        $repo = $this->em->getRepository(Document::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Document::class), $id);
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

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $document = new Document();
            $document->setTitle($params['title']);
            $document->setDescription($params['description']);
            $document->setSpace($space);

            if(!empty($params['facilities'])) {
                /** @var FacilityRepository $facilityRepo */
                $facilityRepo = $this->em->getRepository(Facility::class);

                $facilityIds = array_unique($params['facilities']);
                $facilities = $facilityRepo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityIds);

                if (!empty($facilities)) {
                    $document->setFacilities($facilities);
                } else {
                    $document->setFacilities(null);
                }
            } else {
                $document->setFacilities(null);
            }

            //save file
            $file = new File();

            if (!empty($params['file'])) {
                $parseFile = Parser::parse($params['file']);
                $file->setMimeType($parseFile->getMimeType());
                $file->setType(FileType::TYPE_DOCUMENT);

                $this->validate($file, null, ['api_admin_file_add']);

                $this->em->persist($file);

                $this->s3Service->uploadDocumentFile($file, $params['file']);

                $document->setFile($file);
            } else {
                $document->setFile(null);
            }

            $this->validate($document, null, ['api_admin_document_add']);

            $this->em->persist($document);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $document->getId();
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

            /** @var DocumentRepository $repo */
            $repo = $this->em->getRepository(Document::class);

            /** @var Document $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Document::class), $id);

            if ($entity === null) {
                throw new DocumentNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setDescription($params['description']);
            $entity->setSpace($space);

            $facilities = $entity->getFacilities();
            foreach ($facilities as $facility) {
                $entity->removeFacility($facility);
            }

            if(!empty($params['facilities'])) {
                /** @var FacilityRepository $facilityRepo */
                $facilityRepo = $this->em->getRepository(Facility::class);

                $facilityIds = array_unique($params['facilities']);
                $facilities = $facilityRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityIds);

                if (!empty($facilities)) {
                    $entity->setFacilities($facilities);
                } else {
                    $entity->setFacilities(null);
                }
            } else {
                $entity->setFacilities(null);
            }

            // save file
            $file = $entity->getFile();

            if ($file !== null && empty($params['file'])) {
                $this->s3Service->removeDocumentFile($file->getS3Id(), $file->getType());
            }

            if ($file === null) {
                $file = new File();
            }

            if (!empty($params['file'])) {
                $parseFile = Parser::parse($params['file']);

                $file->setMimeType($parseFile->getMimeType());
                $file->setType(FileType::TYPE_DOCUMENT);

                $this->validate($file, null, ['api_admin_file_edit']);

                $this->em->persist($file);

                $this->s3Service->uploadDocumentFile($file, $params['file']);

                $entity->setFile($file);
            } else {
                $entity->setFile(null);
            }

            $this->validate($entity, null, ['api_admin_document_edit']);

            $this->em->persist($file);

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

            /** @var DocumentRepository $repo */
            $repo = $this->em->getRepository(Document::class);

            /** @var Document $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Document::class), $id);

            if ($entity === null) {
                throw new DocumentNotFoundException();
            }

            $file = $entity->getFile();

            if ($file !== null) {
                $this->s3Service->removeDocumentFile($file->getS3Id(), $file->getType());

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
    public function removeBulk(array $ids)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new DocumentNotFoundException();
            }

            /** @var DocumentRepository $repo */
            $repo = $this->em->getRepository(Document::class);

            $documents = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Document::class), $ids);

            if (empty($documents)) {
                throw new DocumentNotFoundException();
            }

            $fileIds = [];

            /** @var FileRepository $fileRepo */
            $fileRepo = $this->em->getRepository(File::class);

            /**
             * @var Document $document
             */
            foreach ($documents as $document) {
                if ($document->getFile() !== null) {
                    $fileIds[] = $document->getFile()->getId();
                }

                $this->em->remove($document);
            }

            $fileIds = array_unique($fileIds);

            $files = $fileRepo->findByIds($fileIds);

            /**
             * @var File $file
             */
            foreach ($files as $file) {
                $this->s3Service->removeDocumentFile($file->getS3Id(), $file->getType());

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
            throw new DocumentNotFoundException();
        }

        /** @var DocumentRepository $repo */
        $repo = $this->em->getRepository(Document::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Document::class), $ids);

        if (empty($entities)) {
            throw new DocumentNotFoundException();
        }

        return $this->getRelatedData(Document::class, $entities);
    }

    /**
     * @param $id
     * @return array
     */
    public function downloadFile($id): array
    {
        $entity = $this->getById($id);

        if(!empty($entity) && $entity->getFile() !== null) {
            return [$entity->getTitle(), $this->s3Service->downloadDocumentFile($entity->getFile()->getS3Id(), $entity->getFile()->getType())];
        }

        return [null, null];
    }
}
