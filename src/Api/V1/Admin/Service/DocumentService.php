<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DocumentCategoryNotFoundException;
use App\Api\V1\Common\Service\Exception\DocumentNotFoundException;
use App\Api\V1\Common\Service\Exception\FileExtensionException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Document;
use App\Entity\DocumentCategory;
use App\Entity\EmailLog;
use App\Entity\Facility;
use App\Entity\File;
use App\Entity\Role;
use App\Entity\User;
use App\Model\FileType;
use App\Repository\DocumentCategoryRepository;
use App\Repository\DocumentRepository;
use App\Repository\FacilityRepository;
use App\Repository\FileRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
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
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
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

        $isAdmin = false;
        if (!empty($params) || !empty($params[0]['is_admin'])) {
            $isAdmin = $params[0]['is_admin'];
        }

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Document::class), $facilityEntityGrants, $queryBuilder, $isAdmin, $userRoleIds, $categoryId);
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

        $isAdmin = false;
        if (!empty($params) || !empty($params[0]['is_admin'])) {
            $isAdmin = $params[0]['is_admin'];
        }

        $list = $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Document::class), $facilityEntityGrants, $isAdmin, $userRoleIds, $categoryId);

        /** @var Document $entity */
        foreach ($list as $entity) {
            if ($entity !== null && $entity->getFile() !== null) {
                $entity->setDownloadUrl($entity->getFile()->getS3Uri());
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
     * @param string $baseUrl
     * @return int|null
     * @throws \Exception
     */
    public function add(array $params, string $baseUrl): ?int
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
            $document->setSendEmailNotification($params['notification']);

            $ccEmails = !empty($params['emails']) ? $params['emails'] : [];
            $document->setEmails($ccEmails);

            $facilityAll = (bool)$params['facilities_all'];
            if (!empty($params['facilities']) || $facilityAll) {
                /** @var FacilityRepository $facilityRepo */
                $facilityRepo = $this->em->getRepository(Facility::class);

                if ($facilityAll) {
                    $facilities = $facilityRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class));
                } else {
                    $facilityIds = array_unique($params['facilities']);
                    $facilities = $facilityRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityIds);
                }

                if (!empty($facilities)) {
                    $document->setFacilities($facilities);
                } else {
                    $document->setFacilities(null);
                }
            } else {
                $document->setFacilities(null);
            }

            if (!empty($params['roles'])) {
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

                $s3Id = $file->getId() . '.' . MimeUtil::mime2ext($file->getMimeType());
                $file->setS3Id($s3Id);
                $this->em->persist($file);

                $document->setFile($file);
                $this->validate($document, null, ['api_admin_document_add']);

                $this->s3Service->uploadFile($params['file'], $s3Id, $file->getType(), $file->getMimeType());

                //set S3 URI
                $s3Uri = $this->s3Service->getFile($file->getS3Id(), $file->getType());
                $file->setS3Uri($s3Uri);

                $this->em->persist($file);
            } else {
                $document->setFile(null);
            }

            $this->validate($document, null, ['api_admin_document_add']);

            $this->em->persist($document);

            $this->em->flush();

            if ($document->isSendEmailNotification()) {
                $roleIds = array_map(static function (Role $item) {
                    return $item->getId();
                }, $document->getRoles()->toArray());

                /** @var UserRepository $userRepo */
                $userRepo = $this->em->getRepository(User::class);
                $userFacilityIds = $userRepo->getEnabledUserFacilityIdsByRoles($currentSpace, null, $roleIds);

                $emails = [];
                if (!empty($userFacilityIds)) {
                    $facilityIds = array_map(static function (Facility $item) {
                        return $item->getId();
                    }, $document->getFacilities()->toArray());

                    foreach ($userFacilityIds as $userFacilityId) {
                        if ($userFacilityId['facilityIds'] === null) {
                            $emails[] = $userFacilityId['email'];
                        } else {
                            $explodedUserFacilityIds = explode(',', $userFacilityId['facilityIds']);

                            if (!empty(array_intersect($explodedUserFacilityIds, $facilityIds))) {
                                $emails[] = $userFacilityId['email'];
                            }
                        }
                    }
                }

                if (!empty($document->getEmails())) {
                    $emails = array_merge($emails, $ccEmails);
                    $emails = array_unique($emails);
                }

                if (!empty($emails)) {
                    $spaceName = '';
                    if ($document->getCategory() !== null && $document->getCategory()->getSpace() !== null) {
                        $spaceName = $document->getCategory()->getSpace()->getName();
                    }

                    $subject = 'New Document - ' . $document->getTitle();

                    $body = $this->container->get('templating')->render('@api_email/document.html.twig', array(
                        'subject' => $subject,
                        'description' => $document->getDescription(),
                        'spaceName' => $spaceName,
                        'title' => $document->getTitle(),
                        'id' => $document->getId(),
                        'baseUrl' => $baseUrl
                    ));

                    $status = $this->mailer->sendDocumentNotification($emails, $subject, $body, $spaceName);

                    $emailLog = new EmailLog();
                    $emailLog->setSuccess($status);
                    $emailLog->setSubject($subject);
                    $emailLog->setSpace($spaceName);
                    $emailLog->setEmails($emails);

                    $this->em->persist($emailLog);
                    $this->em->flush();
                }
            }

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
     * @param string $baseUrl
     * @throws \Exception
     */
    public function edit($id, array $params, string $baseUrl): void
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
            $entity->setSendEmailNotification($params['notification']);

            $ccEmails = !empty($params['emails']) ? $params['emails'] : [];
            $entity->setEmails($ccEmails);

            $facilities = $entity->getFacilities();
            foreach ($facilities as $facility) {
                $entity->removeFacility($facility);
            }

            $facilityAll = (bool)$params['facilities_all'];
            if (!empty($params['facilities']) || $facilityAll) {
                /** @var FacilityRepository $facilityRepo */
                $facilityRepo = $this->em->getRepository(Facility::class);

                if ($facilityAll) {
                    $facilities = $facilityRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class));
                } else {
                    $facilityIds = array_unique($params['facilities']);
                    $facilities = $facilityRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityIds);
                }

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

            if (!empty($params['roles'])) {
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

                    $s3Id = $file->getId() . '.' . MimeUtil::mime2ext($file->getMimeType());
                    $file->setS3Id($s3Id);
                    $this->em->persist($file);

                    $entity->setFile($file);
                    $this->validate($entity, null, ['api_admin_document_edit']);

                    $this->s3Service->uploadFile($params['file'], $s3Id, $file->getType(), $file->getMimeType());

                    //set S3 URI
                    $s3Uri = $this->s3Service->getFile($file->getS3Id(), $file->getType());
                    $file->setS3Uri($s3Uri);

                    $this->em->persist($file);
                }
            } else {
                $entity->setFile(null);
            }

            $this->validate($entity, null, ['api_admin_document_edit']);

            $this->em->persist($entity);

            $this->em->flush();

            if ($entity->isSendEmailNotification()) {
                $roleIds = array_map(static function (Role $item) {
                    return $item->getId();
                }, $entity->getRoles()->toArray());

                /** @var UserRepository $userRepo */
                $userRepo = $this->em->getRepository(User::class);
                $userFacilityIds = $userRepo->getEnabledUserFacilityIdsByRoles($currentSpace, null, $roleIds);

                $emails = [];
                if (!empty($userFacilityIds)) {
                    $facilityIds = array_map(static function (Facility $item) {
                        return $item->getId();
                    }, $entity->getFacilities()->toArray());

                    foreach ($userFacilityIds as $userFacilityId) {
                        if ($userFacilityId['facilityIds'] === null) {
                            $emails[] = $userFacilityId['email'];
                        } else {
                            $explodedUserFacilityIds = explode(',', $userFacilityId['facilityIds']);

                            if (!empty(array_intersect($explodedUserFacilityIds, $facilityIds))) {
                                $emails[] = $userFacilityId['email'];
                            }
                        }
                    }
                }

                if (!empty($entity->getEmails())) {
                    $emails = array_merge($emails, $ccEmails);
                    $emails = array_unique($emails);
                }

                if (!empty($emails)) {
                    $spaceName = '';
                    if ($entity->getCategory() !== null && $entity->getCategory()->getSpace() !== null) {
                        $spaceName = $entity->getCategory()->getSpace()->getName();
                    }

                    $subject = 'New Document - ' . $entity->getTitle();

                    $body = $this->container->get('templating')->render('@api_email/document.html.twig', array(
                        'subject' => $subject,
                        'description' => $entity->getDescription(),
                        'spaceName' => $spaceName,
                        'title' => $entity->getTitle(),
                        'id' => $entity->getId(),
                        'baseUrl' => $baseUrl
                    ));

                    $status = $this->mailer->sendDocumentNotification($emails, $subject, $body, $spaceName);

                    $emailLog = new EmailLog();
                    $emailLog->setSuccess($status);
                    $emailLog->setSubject($subject);
                    $emailLog->setSpace($spaceName);
                    $emailLog->setEmails($emails);

                    $this->em->persist($emailLog);
                    $this->em->flush();
                }
            }

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

        if (!empty($entity) && $entity->getFile() !== null) {
            return [$entity->getTitle(), $entity->getFile()->getMimeType(), $this->s3Service->downloadFile($entity->getFile()->getS3Id(), $entity->getFile()->getType())];
        }

        return [null, null, null];
    }
}
