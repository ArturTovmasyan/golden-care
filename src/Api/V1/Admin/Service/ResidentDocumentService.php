<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ResidentDocumentNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Resident;
use App\Entity\ResidentDocument;
use App\Entity\ResidentDocumentFile;
use App\Repository\ResidentDocumentFileRepository;
use App\Repository\ResidentDocumentRepository;
use App\Repository\ResidentRepository;
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
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
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

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDocument::class), $residentId);
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
     * @throws \Exception
     */
    public function add(array $params) : ?int
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

            $this->validate($residentDocument, null, ['api_admin_resident_document_add']);

            $this->em->persist($residentDocument);

            //save file
            $file = new ResidentDocumentFile();

            $file->setResidentDocument($residentDocument);

            if (!empty($params['file'])) {
                $parseFile = Parser::parse($params['file']);
                $file->setFile($parseFile->getData());
            } else {
                $file->setFile(null);
            }

            $this->validate($file, null, ['api_admin_resident_document_file_add']);

            $this->em->persist($file);

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
     * @throws \Exception
     */
    public function edit($id, array $params) : void
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

            $this->validate($entity, null, ['api_admin_resident_document_edit']);

            $this->em->persist($entity);

            // save file
            /** @var ResidentDocumentFileRepository $fileRepo */
            $fileRepo = $this->em->getRepository(ResidentDocumentFile::class);

            $file = $fileRepo->getBy($entity->getId());

            if ($file === null) {
                $file = new ResidentDocumentFile();
            }

            $file->setResidentDocument($entity);

            if (!empty($params['file'])) {
                $parseFile = Parser::parse($params['file']);
                $file->setFile($parseFile->getData());
            } else {
                $file->setFile(null);
            }

            $this->validate($file, null, ['api_admin_resident_document_file_edit']);

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

            /** @var ResidentDocumentRepository $repo */
            $repo = $this->em->getRepository(ResidentDocument::class);

            /** @var ResidentDocument $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDocument::class), $id);

            if ($entity === null) {
                throw new ResidentDocumentNotFoundException();
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

            /**
             * @var ResidentDocument $residentDocument
             */
            foreach ($residentDocuments as $residentDocument) {
                $this->em->remove($residentDocument);
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
    public function getSingleFile($id)
    {
        $entity = $this->getById($id);

        if(!empty($entity) && $entity->getFile() !== null) {
            return [$entity->getTitle(), $entity->getFile()->getFile()];
        }

        return [null, null];
    }
}
