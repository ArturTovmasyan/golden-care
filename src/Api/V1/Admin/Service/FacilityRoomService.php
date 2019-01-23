<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CanNotRemoveBadException;
use App\Api\V1\Common\Service\Exception\FacilityRoomNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ContractAction;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Facility;
use App\Model\ContractType;
use Doctrine\Common\Collections\ArrayCollection;
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
        $vacant = false;
        if (!empty($params) && !empty($params[0]['vacant']) && $params[0]['vacant'] === 1) {
            $vacant = true;
        }

        if (!empty($params) && !empty($params[0]['facility_id'])) {
            $facilityId = $params[0]['facility_id'];

            $rooms = $this->em->getRepository(FacilityRoom::class)->findBy(['facility' => $facilityId]);
        } else {
            $rooms = $this->em->getRepository(FacilityRoom::class)->findAll();
        }

        if (!empty($rooms)) {

            $roomIds = array_map(function(FacilityRoom $item){return $item->getId();} , $rooms);

            $facilityBeds = $this->em->getRepository(FacilityBed::class)->getBedIdsByRooms($roomIds);
            $bedIds = [];
            if (\count($facilityBeds)) {
                $bedIds = array_map(function($item){return $item['id'];} , $facilityBeds);
            }

            if ($vacant) {
                $contractActions = $this->em->getRepository(ContractAction::class)->getBeds(ContractType::TYPE_FACILITY, $bedIds);

                $occupancyBedIds = [];
                if (!empty($contractActions)) {
                    $occupancyBedIds = array_map(function($item){return $item['bedId'];} , $contractActions);
                }

                /** @var FacilityRoom $room */
                foreach ($rooms as $room) {
                    $beds = $room->getBeds();

                    if (\count($beds)) {
                        /** @var FacilityBed $bed */
                        foreach ($beds as $bed) {
                            if (\in_array($bed->getId(), $occupancyBedIds, false)) {
                                $room->removeBed($bed);
                            }
                        }
                    }
                }
            } else {
                $contractActions = $this->em->getRepository(ContractAction::class)->getResidentsByBeds(ContractType::TYPE_FACILITY, $bedIds);

                $actions = [];
                if (!empty($contractActions)) {
                    foreach ($contractActions as $contractAction) {
                        $actions[$contractAction['bedId']] = $contractAction['action']->getContract()->getResident();
                    }
                }

                /** @var FacilityRoom $room */
                foreach ($rooms as $room) {
                    $beds = $room->getBeds();

                    if (\count($beds)) {
                        /** @var FacilityBed $bed */
                        foreach ($beds as $bed) {
                            if (!empty($actions[$bed->getId()])) {
                                $bed->setResident($actions[$bed->getId()]);
                            }
                        }
                    }
                }
            }
        }

        return $rooms;

    }

    /**
     * @param $id
     * @return FacilityRoom|null|object
     */
    public function getById($id)
    {
        $room = $this->em->getRepository(FacilityRoom::class)->find($id);

        if ($room !== null) {
            /** @var ArrayCollection $beds */
            $beds = $room->getBeds();

            if ($beds !== null) {
                $ids = array_map(function(FacilityBed $item){return $item->getId();} , $beds->toArray());

                $contractActions = $this->em->getRepository(ContractAction::class)->getResidentsByBeds(ContractType::TYPE_FACILITY, $ids);

                $actions = [];
                if (!empty($contractActions)) {
                    foreach ($contractActions as $contractAction) {
                        $actions[$contractAction['bedId']] = $contractAction['action']->getContract()->getResident();
                    }
                }

                /** @var FacilityBed $bed */
                foreach ($beds as $bed) {
                    if (!empty($actions[$bed->getId()])) {
                        $bed->setResident($actions[$bed->getId()]);
                    }
                }
            }
        }

        return $room;
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
            $facilityRoom->setFloor($params['floor']);
            $facilityRoom->setNotes($params['notes']);

            if (!empty($params['beds'])) {
                foreach ($params['beds'] as $bed) {
                    $newBed = new FacilityBed();
                    $newBed->setNumber($bed['number']);
                    $newBed->setRoom($facilityRoom);
                    $facilityRoom->addBed($newBed);

                    $this->em->persist($newBed);
                }
            }

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
            $entity->setFloor($params['floor']);
            $entity->setNotes($params['notes']);

            $addedBeds = [];
            $editedBeds = [];
            $editedBedsIds = [];
            if (!empty($params['beds'])) {
                foreach ($params['beds'] as $bed) {
                    if (empty($bed['id'])) {
                        $addedBeds[] = $bed;
                    } else {
                        $editedBeds[$bed['id']] = $bed['number'];
                        $editedBedsIds[] = $bed['id'];
                    }
                }
            }

            if ($entity->getBeds() !== null) {
                /** @var FacilityBed $existingBed */
                foreach ($entity->getBeds() as $existingBed) {
                    if (\in_array($existingBed->getId(), $editedBedsIds, false)) {
                        $existingBed->setNumber($editedBeds[$existingBed->getId()]);

                        $this->em->persist($existingBed);
                    } else {
                        $action = $this->em->getRepository(ContractAction::class)->getResidentByBed(ContractType::TYPE_FACILITY, $existingBed->getId());

                        if ($action !== null) {
                            throw new CanNotRemoveBadException();
                        }

                        $entity->removeBed($existingBed);
                        $this->em->remove($existingBed);
                    }
                }
            }

            if (!empty($addedBeds)) {
                foreach ($addedBeds as $bed) {
                    $newBed = new FacilityBed();
                    $newBed->setNumber($bed['number']);
                    $newBed->setRoom($entity);
                    $entity->addBed($newBed);

                    $this->em->persist($newBed);
                }
            }

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
