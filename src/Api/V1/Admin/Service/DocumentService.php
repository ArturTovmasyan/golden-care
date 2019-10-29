<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DocumentCategoryNotFoundException;
use App\Api\V1\Common\Service\Exception\DocumentNotFoundException;
use App\Api\V1\Common\Service\Exception\FileExtensionException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Document;
use App\Entity\DocumentCategory;
use App\Entity\Facility;
use App\Entity\File;
use App\Entity\Role;
use App\Model\FileType;
use App\Repository\DocumentCategoryRepository;
use App\Repository\DocumentRepository;
use App\Repository\FacilityRepository;
use App\Repository\FileRepository;
use App\Repository\RoleRepository;
use App\Util\MimeUtil;
use App\Util\StringUtil;
use DataURI\Parser;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DocumentService
 * @package App\Api\V1\Admin\Service
 */
class DocumentService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var DocumentRepository $repo */
        $repo = $this->em->getRepository(Document::class);

        $facilityEntityGrants = !empty($this->grantService->getCurrentUserEntityGrants(Facility::class)) ? $this->grantService->getCurrentUserEntityGrants(Facility::class) : null;

        $categoryId = null;
        if (!empty($params) || !empty($params[0]['category_id'])) {
            $categoryId = $params[0]['category_id'];
        }

        $userRoleIds = null;
        if (!empty($params) || !empty($params[0]['user_role_ids'])) {
            $userRoleIds = $params[0]['user_role_ids'];
        }

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Document::class), $facilityEntityGrants, $queryBuilder, $userRoleIds, $categoryId);
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

        $categoryId = null;
        if (!empty($params) || !empty($params[0]['category_id'])) {
            $categoryId = $params[0]['category_id'];
        }

        $userRoleIds = null;
        if (!empty($params) || !empty($params[0]['user_role_ids'])) {
            $userRoleIds = $params[0]['user_role_ids'];
        }

        $list = $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Document::class), $facilityEntityGrants, $userRoleIds, $categoryId);

        /** @var Document $entity */
        foreach ($list as $entity) {
            if ($entity !== null && $entity->getFile() !== null) {
                $cmd = $this->s3Service->getS3Client()->getCommand('GetObject', [
                    'Bucket' => getenv('AWS_BUCKET'),
                    'Key' => $entity->getFile()->getType() . '/' . $entity->getFile()->getS3Id(),
                ]);
                $request = $this->s3Service->getS3Client()->createPresignedRequest($cmd, '+20 minutes');

                $entity->setDownloadUrl((string)$request->getUri());
            } else {
                $entity->setDownloadUrl(null);
            }
        }

        return $list;
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
     * @throws \Throwable
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var DocumentCategoryRepository $categoryRepo */
            $categoryRepo = $this->em->getRepository(DocumentCategory::class);

            /** @var DocumentCategory $category */
            $category = $categoryRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(DocumentCategory::class), $params['category_id']);

            if ($category === null) {
                throw new DocumentCategoryNotFoundException();
            }

            $document = new Document();
            $document->setCategory($category);
            $document->setTitle($params['title']);
            $document->setDescription($params['description']);

            if(!empty($params['facilities'])) {
                /** @var FacilityRepository $facilityRepo */
                $facilityRepo = $this->em->getRepository(Facility::class);

                $facilityIds = array_unique($params['facilities']);
                $facilities = $facilityRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityIds);

                if (!empty($facilities)) {
                    $document->setFacilities($facilities);
                } else {
                    $document->setFacilities(null);
                }
            } else {
                $document->setFacilities(null);
            }

            if(!empty($params['roles'])) {
                /** @var RoleRepository $roleRepo */
                $roleRepo = $this->em->getRepository(Role::class);

                $roleIds = array_unique($params['roles']);
                $roles = $roleRepo->findByIds($roleIds);

                if (!empty($roles)) {
                    $document->setRoles($roles);
                } else {
                    $document->setRoles(null);
                }
            } else {
                $document->setRoles(null);
            }

            //save file
            $file = new File();

            if (!empty($params['file'])) {
                $parseFile = Parser::parse($params['file']);
                $file->setMimeType($parseFile->getMimeType());
                $file->setType(FileType::TYPE_DOCUMENT);

                $this->validate($file, null, ['api_admin_file_add']);

                $this->em->persist($file);

                //validate file
                $pdfFileService = $this->container->getParameter('pdf_file_service');
                if (!\in_array(MimeUtil::mime2ext($file->getMimeType()), $pdfFileService['extensions'], false)) {
                    throw new FileExtensionException();
                }

                $s3Id = $file->getId().'.'.MimeUtil::mime2ext($file->getMimeType());
                $file->setS3Id($s3Id);
                $this->em->persist($file);

                $document->setFile($file);
                $this->validate($document, null, ['api_admin_document_add']);

                $this->s3Service->uploadFile($params['file'], $s3Id, $file->getType(), $file->getMimeType());
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
     * @throws \Throwable
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

            /** @var DocumentCategoryRepository $categoryRepo */
            $categoryRepo = $this->em->getRepository(DocumentCategory::class);

            /** @var DocumentCategory $category */
            $category = $categoryRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(DocumentCategory::class), $params['category_id']);

            if ($category === null) {
                throw new DocumentCategoryNotFoundException();
            }

            $entity->setCategory($category);
            $entity->setTitle($params['title']);
            $entity->setDescription($params['description']);

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

            $roles = $entity->getRoles();
            foreach ($roles as $role) {
                $entity->removeRole($role);
            }

            if(!empty($params['roles'])) {
                /** @var RoleRepository $roleRepo */
                $roleRepo = $this->em->getRepository(Role::class);

                $roleIds = array_unique($params['roles']);
                $roles = $roleRepo->findByIds($roleIds);

                if (!empty($roles)) {
                    $entity->setRoles($roles);
                } else {
                    $entity->setRoles(null);
                }
            } else {
                $entity->setRoles(null);
            }

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
                    $file->setType(FileType::TYPE_DOCUMENT);

                    $this->validate($file, null, ['api_admin_file_edit']);

                    $this->em->persist($file);

                    //validate file
                    $pdfFileService = $this->container->getParameter('pdf_file_service');
                    if (!\in_array(MimeUtil::mime2ext($file->getMimeType()), $pdfFileService['extensions'], false)) {
                        throw new FileExtensionException();
                    }

                    $s3Id = $file->getId().'.'.MimeUtil::mime2ext($file->getMimeType());
                    $file->setS3Id($s3Id);
                    $this->em->persist($file);

                    $entity->setFile($file);
                    $this->validate($entity, null, ['api_admin_document_edit']);

                    $this->s3Service->uploadFile($params['file'], $s3Id, $file->getType(), $file->getMimeType());
                }
            } else {
                $entity->setFile(null);
            }

            $this->validate($entity, null, ['api_admin_document_edit']);

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

            /** @var DocumentRepository $repo */
            $repo = $this->em->getRepository(Document::class);

            /** @var Document $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Document::class), $id);

            if ($entity === null) {
                throw new DocumentNotFoundException();
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
            return [$entity->getTitle(), $entity->getFile()->getMimeType(), $this->s3Service->downloadFile($entity->getFile()->getS3Id(), $entity->getFile()->getType())];
        }

        return [null, null, null];
    }
}
