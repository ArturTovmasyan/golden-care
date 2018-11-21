<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\FacilityRoomNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\FacilityRoom;
use App\Entity\Facility;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityRoomService
 * @package App\Api\V1\Admin\Service
 */
class FacilityRoomService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(FacilityRoom::class)->search($queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['facility_id'])) {
            $facilityId = $params[0]['facility_id'];

            return $this->em->getRepository(FacilityRoom::class)->findBy(['facility' => $facilityId]);
        }

        return $this->em->getRepository(FacilityRoom::class)->findAll();
    }

    /**
     * @param $id
     * @return FacilityRoom|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(FacilityRoom::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $facilityId = $params['facility_id'] ?? 0;

            $facility = null;

            if ($facilityId && $facilityId > 0) {
                /** @var Facility $facility */
                $facility = $this->em->getRepository(Facility::class)->find($facilityId);


                if ($facility === null) {
                    throw new FacilityNotFoundException();
                }
            }

            $facilityRoom = new FacilityRoom();
            $facilityRoom->setFacility($facility);
            $facilityRoom->setNumber($params['number']);
            $facilityRoom->setType($params['type']);
            $facilityRoom->setFloor($params['floor']);
            $facilityRoom->setDisabled($params['disabled']);
            $facilityRoom->setShareable($params['shareable']);
            $facilityRoom->setNotes($params['notes']);

            $this->validate($facilityRoom, null, ['api_admin_facility_room_add']);

            $this->em->persist($facilityRoom);
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

            $this->em->getConnection()->beginTransaction();

            /** @var FacilityRoom $entity */
            $entity = $this->em->getRepository(FacilityRoom::class)->find($id);

            if ($entity === null) {
                throw new FacilityRoomNotFoundException();
            }

            $facilityId = $params['facility_id'] ?? 0;

            $facility = null;

            if ($facilityId && $facilityId > 0) {
                /** @var Facility $facility */
                $facility = $this->em->getRepository(Facility::class)->find($facilityId);


                if ($facility === null) {
                    throw new FacilityNotFoundException();
                }
            }

            $entity->setFacility($facility);
            $entity->setNumber($params['number']);
            $entity->setType($params['type']);
            $entity->setFloor($params['floor']);
            $entity->setDisabled($params['disabled']);
            $entity->setShareable($params['shareable']);
            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_admin_facility_room_edit']);

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

            /** @var FacilityRoom $entity */
            $entity = $this->em->getRepository(FacilityRoom::class)->find($id);

            if ($entity === null) {
                throw new FacilityRoomNotFoundException();
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
                throw new FacilityRoomNotFoundException();
            }

            $facilityRooms = $this->em->getRepository(FacilityRoom::class)->findByIds($ids);

            if (empty($facilityRooms)) {
                throw new FacilityRoomNotFoundException();
            }

            /**
             * @var FacilityRoom $facilityRoom
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($facilityRooms as $facilityRoom) {
                $this->em->remove($facilityRoom);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (FacilityRoomNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
