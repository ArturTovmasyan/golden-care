<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DiningRoomNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\DiningRoom;
use App\Entity\Facility;
use App\Repository\DiningRoomRepository;
use App\Repository\FacilityRepository;
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
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var DiningRoomRepository $repo */
        $repo = $this->em->getRepository(DiningRoom::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DiningRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();
        $entityGrants = $this->grantService->getCurrentUserEntityGrants(DiningRoom::class);
        $facilityEntityGrants = $this->grantService->getCurrentUserEntityGrants(Facility::class);

        /** @var DiningRoomRepository $repo */
        $repo = $this->em->getRepository(DiningRoom::class);

        if (!empty($params) && !empty($params[0]['facility_id'])) {
            $facilityId = $params[0]['facility_id'];

            return $repo->getBy($currentSpace, $entityGrants, $facilityEntityGrants, $facilityId);
        }

        return $repo->list($currentSpace, $entityGrants, $facilityEntityGrants);
    }

    /**
     * @param $id
     * @return DiningRoom|null|object
     */
    public function getById($id)
    {
        /** @var DiningRoomRepository $repo */
        $repo = $this->em->getRepository(DiningRoom::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DiningRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);
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

            $facilityId = $params['facility_id'] ?? 0;

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            /** @var Facility $facility */
            $facility = $facilityRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);

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

            $insert_id = $diningRoom->getId();
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

            /** @var DiningRoomRepository $repo */
            $repo = $this->em->getRepository(DiningRoom::class);

            /** @var DiningRoom $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(DiningRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

            if ($entity === null) {
                throw new DiningRoomNotFoundException();
            }

            $facilityId = $params['facility_id'] ?? 0;

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            /** @var Facility $facility */
            $facility = $facilityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);

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
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var DiningRoomRepository $repo */
            $repo = $this->em->getRepository(DiningRoom::class);

            /** @var DiningRoom $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DiningRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

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
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new DiningRoomNotFoundException();
            }

            /** @var DiningRoomRepository $repo */
            $repo = $this->em->getRepository(DiningRoom::class);

            $diningRooms = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DiningRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $ids);

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

    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new DiningRoomNotFoundException();
        }

        /** @var DiningRoomRepository $repo */
        $repo = $this->em->getRepository(DiningRoom::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DiningRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $ids);

        if (empty($entities)) {
            throw new DiningRoomNotFoundException();
        }

        return $this->getRelatedData(DiningRoom::class, $entities);
    }
}
