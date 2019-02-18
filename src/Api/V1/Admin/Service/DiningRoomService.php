<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DiningRoomNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\DiningRoom;
use App\Entity\Facility;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DiningRoomService
 * @package App\Api\V1\Admin\Service
 */
class DiningRoomService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(DiningRoom::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        if (!empty($params) && !empty($params[0]['facility_id'])) {
            $facilityId = $params[0]['facility_id'];

            return $this->em->getRepository(DiningRoom::class)->getBy($currentSpace, $facilityId);
        }

        return $this->em->getRepository(DiningRoom::class)->list($currentSpace);
    }

    /**
     * @param $id
     * @return DiningRoom|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(DiningRoom::class)->getOne($this->grantService->getCurrentSpace(), $id);
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

            /** @var Facility $facility */
            $facility = $this->em->getRepository(Facility::class)->getOne($this->grantService->getCurrentSpace(), $facilityId);

            if ($facility === null) {
                throw new FacilityNotFoundException();
            }

            $diningRoom = new DiningRoom();
            $diningRoom->setTitle($params['title']);
            $diningRoom->setFacility($facility);

            $this->validate($diningRoom, null, ['api_admin_dining_room_add']);

            $this->em->persist($diningRoom);
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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var DiningRoom $entity */
            $entity = $this->em->getRepository(DiningRoom::class)->getOne($currentSpace, $id);

            if ($entity === null) {
                throw new DiningRoomNotFoundException();
            }

            $facilityId = $params['facility_id'] ?? 0;

            /** @var Facility $facility */
            $facility = $this->em->getRepository(Facility::class)->getOne($currentSpace, $facilityId);

            if ($facility === null) {
                throw new FacilityNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setFacility($facility);

            $this->validate($entity, null, ['api_admin_dining_room_edit']);

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

            /** @var DiningRoom $entity */
            $entity = $this->em->getRepository(DiningRoom::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($entity === null) {
                throw new DiningRoomNotFoundException();
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
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new DiningRoomNotFoundException();
            }

            $diningRooms = $this->em->getRepository(DiningRoom::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

            if (empty($diningRooms)) {
                throw new DiningRoomNotFoundException();
            }

            /**
             * @var DiningRoom $diningRoom
             */
            foreach ($diningRooms as $diningRoom) {
                $this->em->remove($diningRoom);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
