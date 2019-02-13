<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentHavePrimaryPhysicianException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentPhysicianNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Physician;
use App\Entity\Resident;
use App\Entity\ResidentPhysician;
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
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rp.resident = :residentId')
            ->setParameter('residentId', $residentId);

        $this->em->getRepository(ResidentPhysician::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(ResidentPhysician::class)->getBy($this->grantService->getCurrentSpace(), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentPhysician|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResidentPhysician::class)->getOne($this->grantService->getCurrentSpace(), $id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            /**
             * @var Resident $resident
             * @var Physician $physician
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $residentId  = $params['resident_id'] ?? 0;
            $physicianId = $params['physician_id'] ?? 0;
            $primary     = $params['primary'] ? (bool) $params['primary'] : false;

            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $physician = $this->em->getRepository(Physician::class)->getOne($currentSpace, $physicianId);

            if ($physician === null) {
                throw new PhysicianNotFoundException();
            }

            // check unique primary
            if ($primary && $this->em->getRepository(ResidentPhysician::class)->getOneBy($currentSpace, $resident)) {
                throw new ResidentHavePrimaryPhysicianException();
            }

            $residentPhysician = new ResidentPhysician();
            $residentPhysician->setResident($resident);
            $residentPhysician->setPhysician($physician);
            $residentPhysician->setPrimary($primary);

            $this->validate($residentPhysician, null, ['api_admin_resident_physician_add']);

            $this->em->persist($residentPhysician);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Exception
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

            $entity = $this->em->getRepository(ResidentPhysician::class)->getOne($currentSpace, $id);

            if ($entity === null) {
                throw new ResidentPhysicianNotFoundException();
            }

            $residentId  = $params['resident_id'] ?? 0;
            $physicianId = $params['physician_id'] ?? 0;
            $primary     = $params['primary'] ? (bool) $params['primary'] : false;

            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $physician = $this->em->getRepository(Physician::class)->getOne($currentSpace, $physicianId);

            if ($physician === null) {
                throw new PhysicianNotFoundException();
            }

            // check unique primary
            if ($primary) {
                $primaryEntity = $this->em->getRepository(ResidentPhysician::class)->getOneBy($currentSpace, $resident);

                if ($primaryEntity && $primaryEntity->getId() !== $id) {
                    throw new ResidentHavePrimaryPhysicianException();
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResidentPhysician $entity */
            $entity = $this->em->getRepository(ResidentPhysician::class)->getOne($this->grantService->getCurrentSpace(), $id);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            if (empty($ids)) {
                throw new ResidentPhysicianNotFoundException();
            }

            $residentPhysicians = $this->em->getRepository(ResidentPhysician::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

            if (empty($residentPhysicians)) {
                throw new ResidentPhysicianNotFoundException();
            }

            /**
             * @var ResidentPhysician $residentPhysician
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($residentPhysicians as $residentPhysician) {
                $this->em->remove($residentPhysician);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ResidentPhysicianNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
