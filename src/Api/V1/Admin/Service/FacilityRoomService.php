<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ActiveResidentExistInBedException;
use App\Api\V1\Common\Service\Exception\ActiveResidentExistInRoomException;
use App\Api\V1\Common\Service\Exception\CanNotRemoveBadException;
use App\Api\V1\Common\Service\Exception\FacilityRoomNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityRoomTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\InvalidPrivateRoomException;
use App\Api\V1\Common\Service\Exception\InvalidSharedRoomException;
use App\Api\V1\Common\Service\Exception\RoomTypeFacilityChangedException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Facility;
use App\Entity\FacilityRoomType;
use App\Entity\FacilityRoomTypes;
use App\Entity\ResidentAdmission;
use App\Model\AdmissionType;
use App\Model\GroupType;
use App\Repository\FacilityBedRepository;
use App\Repository\FacilityRepository;
use App\Repository\FacilityRoomRepository;
use App\Repository\FacilityRoomTypeRepository;
use App\Repository\ResidentAdmissionRepository;
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
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        $facilityId = null;
        if (!empty($params) || !empty($params[0]['facility_id'])) {
            $facilityId = $params[0]['facility_id'];
        }

        /** @var FacilityRoomRepository $repo */
        $repo = $this->em->getRepository(FacilityRoom::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $queryBuilder, $facilityId);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $vacant = false;
        if (!empty($params) && !empty($params[0]['vacant']) && (int)$params[0]['vacant'] === 1) {
            $vacant = true;
        }

        /** @var FacilityRoomRepository $repo */
        $repo = $this->em->getRepository(FacilityRoom::class);

        if (!empty($params) && !empty($params[0]['facility_id'])) {
            $facilityId = $params[0]['facility_id'];

            $rooms = $repo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);
        } else {
            $rooms = $repo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class));
        }

        if (!empty($rooms)) {

            $roomIds = array_map(static function (FacilityRoom $item) {
                return $item->getId();
            }, $rooms);

            /** @var ResidentAdmissionRepository $admissionRepo */
            $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

            /** @var FacilityBedRepository $bedRepo */
            $bedRepo = $this->em->getRepository(FacilityBed::class);

            $facilityBeds = $bedRepo->getBedIdsByRooms($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityBed::class), $roomIds);
            $bedIds = [];
            if (\count($facilityBeds)) {
                $bedIds = array_map(static function ($item) {
                    return $item['id'];
                }, $facilityBeds);
            }

            if ($vacant) {
                $currentDateFormatted = null;
                if (!empty($params) && !empty($params[0]['date'])) {
                    $currentDate = new \DateTime($params[0]['date']);
                    $currentDateFormatted = $currentDate->format('Y-m-d');
                }

                $residentBedId = null;
                if (!empty($params) && !empty($params[0]['resident_id'])) {
                    $residentId = (int)$params[0]['resident_id'];

                    /** @var ResidentAdmission $lastAction */
                    $lastAction = $admissionRepo->getLastAction($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $residentId);

                    if ($lastAction !== null && $lastAction->getFacilityBed() !== null && $lastAction->getAdmissionType() === AdmissionType::DISCHARGE) {
                        $residentBedId = $lastAction->getFacilityBed()->getId();
                    }
                }

                $residentAdmissions = $admissionRepo->getBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $bedIds);

                $occupancyBedIds = [];
                if (!empty($residentAdmissions)) {
                    $occupancyBedIds = array_map(static function ($item) {
                        return $item['bedId'];
                    }, $residentAdmissions);
                }

                /** @var FacilityRoom $room */
                foreach ($rooms as $room) {
                    $beds = $room->getBeds();

                    if (\count($beds)) {
                        /** @var FacilityBed $bed */
                        foreach ($beds as $bed) {
                            $isNotResidentBed = true;
                            if ($residentBedId !== null && $bed->getId() === $residentBedId) {
                                $isNotResidentBed = false;
                            }

                            if (!$bed->isEnabled() || \in_array($bed->getId(), $occupancyBedIds, false) || ($isNotResidentBed && $bed->getBillThroughDate() !== null && $currentDateFormatted !== null && $currentDateFormatted <= $bed->getBillThroughDate()->format('Y-m-d'))) {
                                $room->removeBed($bed);
                            }
                        }
                    }
                }
            } else {
                $residentAdmissions = $admissionRepo->getResidentsByBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $bedIds);

                $admissions = [];
                if (!empty($residentAdmissions)) {
                    foreach ($residentAdmissions as $residentAdmission) {
                        $admissions[$residentAdmission['bedId']] = $residentAdmission['admission']->getResident();
                    }
                }

                /** @var FacilityRoom $room */
                foreach ($rooms as $room) {
                    $beds = $room->getBeds();

                    if (\count($beds)) {
                        /** @var FacilityBed $bed */
                        foreach ($beds as $bed) {
                            if (!empty($admissions[$bed->getId()])) {
                                $bed->setResident($admissions[$bed->getId()]);
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
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var FacilityRoomRepository $repo */
        $repo = $this->em->getRepository(FacilityRoom::class);

        $room = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

        if ($room !== null) {
            /** @var ArrayCollection $beds */
            $beds = $room->getBeds();

            if ($beds !== null) {
                $ids = array_map(static function (FacilityBed $item) {
                    return $item->getId();
                }, $beds->toArray());

                /** @var ResidentAdmissionRepository $admissionRepo */
                $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

                $residentAdmissions = $admissionRepo->getResidentsByBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $ids);

                $admissions = [];
                if (!empty($residentAdmissions)) {
                    foreach ($residentAdmissions as $residentAdmission) {
                        $admissions[$residentAdmission['bedId']] = $residentAdmission['admission']->getResident();
                    }
                }

                /** @var FacilityBed $bed */
                foreach ($beds as $bed) {
                    if (!empty($admissions[$bed->getId()])) {
                        $bed->setResident($admissions[$bed->getId()]);
                    }
                }
            }
        }

        return $room;
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Exception
     */
    public function add(array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $facilityId = $params['facility_id'] ?? 0;

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            /** @var Facility $facility */
            $facility = $facilityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);

            if ($facility === null) {
                throw new FacilityNotFoundException();
            }

            /** @var FacilityRoomTypeRepository $typeRepo */
            $typeRepo = $this->em->getRepository(FacilityRoomType::class);

            $privateType = null;
            if (!empty($params['private_type_id'])) {
                /** @var FacilityRoomType $privateType */
                $privateType = $typeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $params['private_type_id']);

                if ($privateType === null || ($privateType !== null && !$privateType->isPrivate())) {
                    throw new FacilityRoomTypeNotFoundException();
                }
            }

            $sharedType = null;
            if (!empty($params['shared_type_id'])) {
                /** @var FacilityRoomType $sharedType */
                $sharedType = $typeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $params['shared_type_id']);

                if ($sharedType === null || ($sharedType !== null && $sharedType->isPrivate())) {
                    throw new FacilityRoomTypeNotFoundException();
                }
            }

            if ($privateType === null && $sharedType === null) {
                throw new FacilityRoomTypeNotFoundException();
            }

            $floor = !empty($params['floor']) ? $params['floor'] : null;

            $facilityRoom = new FacilityRoom();
            $facilityRoom->setFacility($facility);
            $facilityRoom->setNumber($params['number']);
            $facilityRoom->setFloor($floor);
            $facilityRoom->setNotes($params['notes']);

            if (!empty($params['beds'])) {
                foreach ($params['beds'] as $bed) {
                    $newBed = new FacilityBed();
                    $newBed->setNumber($bed['number']);
                    $newBed->setRoom($facilityRoom);
                    $newBed->setEnabled($bed['enabled']);
                    $facilityRoom->addBed($newBed);

                    $this->em->persist($newBed);
                }
            }

            $this->validate($facilityRoom, null, ['api_admin_facility_room_add']);

            if ($facilityRoom->getBeds() !== null) {
                $i = 0;
                $disabledBedIds = [];

                /** @var FacilityBed $bed */
                foreach ($facilityRoom->getBeds() as $bed) {
                    if ($bed->isEnabled()) {
                        ++$i;
                    } else {
                        $disabledBedIds[] = $bed->getId();
                    }
                }

                /** @var ResidentAdmissionRepository $admissionRepo */
                $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

                $residentAdmissions = $admissionRepo->getBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $disabledBedIds);

                if (!empty($residentAdmissions)) {
                    throw new ActiveResidentExistInBedException();
                }

                if ($i > 1 && $sharedType === null) {
                    throw new InvalidPrivateRoomException();
                }

                if ($i <= 1 && $privateType === null) {
                    throw new InvalidSharedRoomException();
                }

                if ($i > 1) {
                    $facilityRoom->setType($sharedType);
                } else {
                    $facilityRoom->setType($privateType);
                }

                $type = $facilityRoom->getType();
                if ($facility !== null && $type !== null && $type->getFacility() !== null && $facility->getId() !== $type->getFacility()->getId()) {
                    throw new RoomTypeFacilityChangedException();
                }
            }

            $this->em->persist($facilityRoom);

            $this->addRoomTypes($facilityRoom, $privateType, $sharedType);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $facilityRoom->getId();
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
    public function edit($id, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var FacilityRoomRepository $repo */
            $repo = $this->em->getRepository(FacilityRoom::class);

            /** @var FacilityRoom $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

            if ($entity === null) {
                throw new FacilityRoomNotFoundException();
            }

            $facilityId = $params['facility_id'] ?? 0;

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            /** @var Facility $facility */
            $facility = $facilityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);

            if ($facility === null) {
                throw new FacilityNotFoundException();
            }

            /** @var FacilityRoomTypeRepository $typeRepo */
            $typeRepo = $this->em->getRepository(FacilityRoomType::class);

            $privateType = null;
            if (!empty($params['private_type_id'])) {
                /** @var FacilityRoomType $privateType */
                $privateType = $typeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $params['private_type_id']);

                if ($privateType === null || ($privateType !== null && !$privateType->isPrivate())) {
                    throw new FacilityRoomTypeNotFoundException();
                }
            }

            $sharedType = null;
            if (!empty($params['shared_type_id'])) {
                /** @var FacilityRoomType $sharedType */
                $sharedType = $typeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $params['shared_type_id']);

                if ($sharedType === null || ($sharedType !== null && $sharedType->isPrivate())) {
                    throw new FacilityRoomTypeNotFoundException();
                }
            }

            if ($privateType === null && $sharedType === null) {
                throw new FacilityRoomTypeNotFoundException();
            }

            $floor = !empty($params['floor']) ? $params['floor'] : null;

            $entity->setFacility($facility);
            $entity->setNumber($params['number']);
            $entity->setFloor($floor);
            $entity->setNotes($params['notes']);

            $addedBeds = [];
            $editedBeds = [];
            $editedBedsIds = [];
            if (!empty($params['beds'])) {
                foreach ($params['beds'] as $bed) {
                    if (empty($bed['id'])) {
                        $addedBeds[] = $bed;
                    } else {
                        $editedBeds[$bed['id']] = $bed;
                        $editedBedsIds[] = $bed['id'];
                    }
                }
            }

            if ($entity->getBeds() !== null) {
                /** @var FacilityBed $existingBed */
                foreach ($entity->getBeds() as $existingBed) {
                    if (\in_array($existingBed->getId(), $editedBedsIds, false)) {
                        $existingBed->setNumber($editedBeds[$existingBed->getId()]['number']);
                        $existingBed->setEnabled($editedBeds[$existingBed->getId()]['enabled']);

                        $this->em->persist($existingBed);
                    } else {
                        /** @var ResidentAdmissionRepository $admissionRepo */
                        $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

                        $admission = $admissionRepo->getResidentByBed($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $existingBed->getId());

                        if ($admission !== null) {
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
                    $newBed->setEnabled($bed['enabled']);
                    $newBed->setRoom($entity);
                    $entity->addBed($newBed);

                    $this->em->persist($newBed);
                }
            }

            $this->validate($entity, null, ['api_admin_facility_room_edit']);

            if ($entity->getBeds() !== null) {
                $i = 0;
                $disabledBedIds = [];

                /** @var FacilityBed $bed */
                foreach ($entity->getBeds() as $bed) {
                    if ($bed->isEnabled()) {
                        ++$i;
                    } else {
                        $disabledBedIds[] = $bed->getId();
                    }
                }

                /** @var ResidentAdmissionRepository $admissionRepo */
                $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

                $residentAdmissions = $admissionRepo->getBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $disabledBedIds);

                if (!empty($residentAdmissions)) {
                    throw new ActiveResidentExistInBedException();
                }

                if ($i > 1 && $sharedType === null) {
                    throw new InvalidPrivateRoomException();
                }

                if ($i <= 1 && $privateType === null) {
                    throw new InvalidSharedRoomException();
                }

                if ($i > 1) {
                    $entity->setType($sharedType);
                } else {
                    $entity->setType($privateType);
                }

                $type = $entity->getType();
                if ($facility !== null && $type !== null && $type->getFacility() !== null && $facility->getId() !== $type->getFacility()->getId()) {
                    throw new RoomTypeFacilityChangedException();
                }
            }

            $this->em->persist($entity);

            $this->addRoomTypes($entity, $privateType, $sharedType);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param FacilityRoom $entity
     * @param FacilityRoomType|null $privateType
     * @param FacilityRoomType|null $sharedType
     */
    private function addRoomTypes(FacilityRoom $entity, FacilityRoomType $privateType = null, FacilityRoomType $sharedType = null)
    {
        if ($entity->getTypes() !== null) {
            /** @var FacilityRoomTypes $existingType */
            foreach ($entity->getTypes() as $existingType) {
                $this->em->remove($existingType);
            }
        }

        if ($privateType !== null) {
            $privateRoomType = new FacilityRoomTypes();
            $privateRoomType->setRoom($entity);
            $privateRoomType->setType($privateType);

            $this->em->persist($privateRoomType);
        }

        if ($sharedType !== null) {
            $sharedRoomType = new FacilityRoomTypes();
            $sharedRoomType->setRoom($entity);
            $sharedRoomType->setType($sharedType);

            $this->em->persist($sharedRoomType);
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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var FacilityRoomRepository $repo */
            $repo = $this->em->getRepository(FacilityRoom::class);

            /** @var FacilityRoom $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

            if ($entity === null) {
                throw new FacilityRoomNotFoundException();
            }

            if ($entity->getBeds() !== null) {
                $bedIds = [];

                /** @var FacilityBed $bed */
                foreach ($entity->getBeds() as $bed) {
                    $bedIds[] = $bed->getId();
                }

                /** @var ResidentAdmissionRepository $admissionRepo */
                $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

                $residentAdmissions = $admissionRepo->getAdmissionBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $bedIds);

                if (!empty($residentAdmissions)) {
                    throw new ActiveResidentExistInRoomException();
                }
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
                throw new FacilityRoomNotFoundException();
            }

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var FacilityRoomRepository $repo */
            $repo = $this->em->getRepository(FacilityRoom::class);

            $facilityRooms = $repo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $ids);

            if (empty($facilityRooms)) {
                throw new FacilityRoomNotFoundException();
            }

            /**
             * @var FacilityRoom $facilityRoom
             */
            foreach ($facilityRooms as $facilityRoom) {
                if ($facilityRoom->getBeds() !== null) {
                    $bedIds = [];

                    /** @var FacilityBed $bed */
                    foreach ($facilityRoom->getBeds() as $bed) {
                        $bedIds[] = $bed->getId();
                    }

                    /** @var ResidentAdmissionRepository $admissionRepo */
                    $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

                    $residentAdmissions = $admissionRepo->getAdmissionBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $bedIds);

                    if (!empty($residentAdmissions)) {
                        throw new ActiveResidentExistInRoomException();
                    }
                }

                $this->em->remove($facilityRoom);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param int $facilityId
     * @return mixed
     */
    public function getLastNumber($facilityId)
    {
        /** @var FacilityRoomRepository $repo */
        $repo = $this->em->getRepository(FacilityRoom::class);

        $max_number = $repo->getLastNumber($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $facilityId);

        return $max_number ? $max_number['max_room_number'] : null;
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new FacilityRoomNotFoundException();
        }

        /** @var FacilityRoomRepository $repo */
        $repo = $this->em->getRepository(FacilityRoom::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $ids);

        if (empty($entities)) {
            throw new FacilityRoomNotFoundException();
        }

        return $this->getRelatedData(FacilityRoom::class, $entities);
    }
}
