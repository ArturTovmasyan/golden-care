<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentPhysicianNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Physician;
use App\Entity\Resident;
use App\Entity\ResidentPhysician;
use App\Repository\PhysicianRepository;
use App\Repository\ResidentPhysicianRepository;
use App\Repository\ResidentRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentPhysicianService
 * @package App\Api\V1\Admin\Service
 */
class ResidentPhysicianService extends BaseService implements IGridService
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
            ->where('rp.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentPhysicianRepository $repo */
        $repo = $this->em->getRepository(ResidentPhysician::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentPhysicianRepository $repo */
            $repo = $this->em->getRepository(ResidentPhysician::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentPhysician|null|object
     */
    public function getById($id)
    {
        /** @var ResidentPhysicianRepository $repo */
        $repo = $this->em->getRepository(ResidentPhysician::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $id);
    }

    /**
     * @param $resident_id
     * @return ResidentPhysician|null|object
     */
    public function getPrimaryByResidentId($resident_id)
    {
        /** @var ResidentPhysicianRepository $repo */
        $repo = $this->em->getRepository(ResidentPhysician::class);

        return $repo->getOnePrimaryByResidentId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $resident_id);
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
            /**
             * @var Resident $resident
             * @var Physician $physician
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $residentId  = $params['resident_id'] ?? 0;
            $physicianId = $params['physician_id'] ?? 0;
            $primary = $params['primary'] ? (bool) $params['primary'] : false;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            /** @var PhysicianRepository $physicianRepo */
            $physicianRepo = $this->em->getRepository(Physician::class);

            /** @var Physician $physician */
            $physician = $physicianRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Physician::class), $physicianId);

            if ($physician === null) {
                throw new PhysicianNotFoundException();
            }

            // check unique primary
            if ($primary) {
                /** @var ResidentPhysicianRepository $repo */
                $repo = $this->em->getRepository(ResidentPhysician::class);

                $primary_physicians = $repo->getPrimariesByResidentId($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $residentId);

                /** @var ResidentPhysician $primary_physician */
                foreach ($primary_physicians as $primary_physician) {
                    $primary_physician->setPrimary(false);
                    $this->em->persist($primary_physician);
                }
            }

            $residentPhysician = new ResidentPhysician();
            $residentPhysician->setSortOrder(0);
            $residentPhysician->setResident($resident);
            $residentPhysician->setPhysician($physician);
            $residentPhysician->setPrimary($primary);

            $this->validate($residentPhysician, null, ['api_admin_resident_physician_add']);

            $this->em->persist($residentPhysician);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentPhysician->getId();
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
            /**
             * @var ResidentPhysician $entity
             * @var Resident $resident
             * @var Physician $physician
             * @var ResidentPhysician $primaryEntity
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentPhysicianRepository $repo */
            $repo = $this->em->getRepository(ResidentPhysician::class);

            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $id);

            if ($entity === null) {
                throw new ResidentPhysicianNotFoundException();
            }

            $residentId  = $params['resident_id'] ?? 0;
            $physicianId = $params['physician_id'] ?? 0;
            $primary = $params['primary'] ? (bool) $params['primary'] : false;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            /** @var PhysicianRepository $physicianRepo */
            $physicianRepo = $this->em->getRepository(Physician::class);

            /** @var Physician $physician */
            $physician = $physicianRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Physician::class), $physicianId);

            if ($physician === null) {
                throw new PhysicianNotFoundException();
            }

            // check unique primary
            if ($primary) {
                $primary_physicians = $repo->getPrimariesByResidentId($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $residentId);

                /** @var ResidentPhysician $primary_physician */
                foreach ($primary_physicians as $primary_physician) {
                    $primary_physician->setPrimary(false);
                    $this->em->persist($primary_physician);
                }
            }

            $entity->setResident($resident);
            $entity->setPhysician($physician);
            $entity->setPrimary($primary);

            $this->validate($entity, null, ['api_admin_resident_physician_edit']);

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

            /** @var ResidentPhysicianRepository $repo */
            $repo = $this->em->getRepository(ResidentPhysician::class);

            /** @var ResidentPhysician $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $id);

            if ($entity === null) {
                throw new ResidentPhysicianNotFoundException();
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
                throw new ResidentPhysicianNotFoundException();
            }

            /** @var ResidentPhysicianRepository $repo */
            $repo = $this->em->getRepository(ResidentPhysician::class);

            $residentPhysicians = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $ids);

            if (empty($residentPhysicians)) {
                throw new ResidentPhysicianNotFoundException();
            }

            /**
             * @var ResidentPhysician $residentPhysician
             */
            foreach ($residentPhysicians as $residentPhysician) {
                $this->em->remove($residentPhysician);
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
            throw new ResidentPhysicianNotFoundException();
        }

        /** @var ResidentPhysicianRepository $repo */
        $repo = $this->em->getRepository(ResidentPhysician::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $ids);

        if (empty($entities)) {
            throw new ResidentPhysicianNotFoundException();
        }

        return $this->getRelatedData(ResidentPhysician::class, $entities);
    }

    /**
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function reorder(array $params)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (!empty($params) && !empty($params['physicians'])) {
                /** @var ResidentPhysicianRepository $repo */
                $repo = $this->em->getRepository(ResidentPhysician::class);

                foreach ($params['physicians'] as $idx => $value) {
                    /** @var ResidentPhysician $rp */
                    $rp = $repo->getOne(
                        $this->grantService->getCurrentSpace(),
                        $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class),
                        $value['id']
                    );

                    if (!empty($rp)) {
                        $rp->setSortOrder($idx);
                        $this->em->persist($rp);
                    }
                }

            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
