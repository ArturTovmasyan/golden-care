<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ApartmentRoomNotFoundException;
use App\Api\V1\Common\Service\Exception\ApartmentNotFoundException;
use App\Api\V1\Common\Service\Exception\CanNotRemoveBadException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\Apartment;
use App\Entity\ResidentAdmission;
use App\Model\GroupType;
use App\Repository\ApartmentBedRepository;
use App\Repository\ApartmentRepository;
use App\Repository\ApartmentRoomRepository;
use App\Repository\ResidentAdmissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ApartmentRoomService
 * @package App\Api\V1\Admin\Service
 */
class ApartmentRoomService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var ApartmentRoomRepository $repo */
        $repo = $this->em->getRepository(ApartmentRoom::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $queryBuilder);
    }

    public function list($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $vacant = false;
        if (!empty($params) && !empty($params[0]['vacant']) && (int)$params[0]['vacant'] === 1) {
            $vacant = true;
        }

        /** @var ApartmentRoomRepository $repo */
        $repo = $this->em->getRepository(ApartmentRoom::class);

        if (!empty($params) && !empty($params[0]['apartment_id'])) {
            $apartmentId = $params[0]['apartment_id'];

            $rooms = $repo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $apartmentId);
        } else {
            $rooms = $repo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class));
        }

        if (!empty($rooms)) {

            $roomIds = array_map(function(ApartmentRoom $item){return $item->getId();} , $rooms);

            /** @var ResidentAdmissionRepository $admissionRepo */
            $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

            /** @var ApartmentBedRepository $bedRepo */
            $bedRepo = $this->em->getRepository(ApartmentBed::class);

            $apartmentBeds = $bedRepo->getBedIdsByRooms($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentBed::class), $roomIds);
            $bedIds = [];
            if (\count($apartmentBeds)) {
                $bedIds = array_map(function($item){return $item['id'];} , $apartmentBeds);
            }

            if ($vacant) {
                $residentAdmissions = $admissionRepo->getBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_APARTMENT, $bedIds);

                $occupancyBedIds = [];
                if (!empty($residentAdmissions)) {
                    $occupancyBedIds = array_map(function($item){return $item['bedId'];} , $residentAdmissions);
                }

                /** @var ApartmentRoom $room */
                foreach ($rooms as $room) {
                    $beds = $room->getBeds();

                    if (\count($beds)) {
                        /** @var ApartmentBed $bed */
                        foreach ($beds as $bed) {
                            if (!$bed->isEnabled() || \in_array($bed->getId(), $occupancyBedIds, false)) {
                                $room->removeBed($bed);
                            }
                        }
                    }
                }
            } else {
                $residentAdmissions = $admissionRepo->getResidentsByBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_APARTMENT, $bedIds);

                $admissions = [];
                if (!empty($residentAdmissions)) {
                    foreach ($residentAdmissions as $residentAdmission) {
                        $admissions[$residentAdmission['bedId']] = $residentAdmission['admission']->getResident();
                    }
                }

                /** @var ApartmentRoom $room */
                foreach ($rooms as $room) {
                    $beds = $room->getBeds();

                    if (\count($beds)) {
                        /** @var ApartmentBed $bed */
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
     * @return ApartmentRoom|null|object
     */
    public function getById($id)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var ApartmentRoomRepository $repo */
        $repo = $this->em->getRepository(ApartmentRoom::class);

        $room = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $id);

        if ($room !== null) {
            /** @var ArrayCollection $beds */
            $beds = $room->getBeds();

            if ($beds !== null) {
                $ids = array_map(function(ApartmentBed $item){return $item->getId();} , $beds->toArray());

                /** @var ResidentAdmissionRepository $admissionRepo */
                $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

                $residentAdmissions = $admissionRepo->getResidentsByBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_APARTMENT, $ids);

                $admissions = [];
                if (!empty($residentAdmissions)) {
                    foreach ($residentAdmissions as $residentAdmission) {
                        $admissions[$residentAdmission['bedId']] = $residentAdmission['admission']->getResident();
                    }
                }

                /** @var ApartmentBed $bed */
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
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $apartmentId = $params['apartment_id'] ?? 0;

            /** @var ApartmentRepository $apartmentRepo */
            $apartmentRepo = $this->em->getRepository(Apartment::class);

            /** @var Apartment $apartment */
            $apartment = $apartmentRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $apartmentId);

            if ($apartment === null) {
                throw new ApartmentNotFoundException();
            }

            $apartmentRoom = new ApartmentRoom();
            $apartmentRoom->setApartment($apartment);
            $apartmentRoom->setNumber($params['number']);
            $apartmentRoom->setFloor($params['floor']);
            $apartmentRoom->setNotes($params['notes']);

            if (!empty($params['beds'])) {
                foreach ($params['beds'] as $bed) {
                    $newBed = new ApartmentBed();
                    $newBed->setNumber($bed['number']);
                    $newBed->setEnabled($bed['enabled']);
                    $newBed->setRoom($apartmentRoom);
                    $apartmentRoom->addBed($newBed);

                    $this->em->persist($newBed);
                }
            }

            $this->validate($apartmentRoom, null, ['api_admin_apartment_room_add']);

            if ($apartmentRoom->getBeds() !== null) {
                $i = 0;

                /** @var ApartmentBed $bed */
                foreach ($apartmentRoom->getBeds() as $bed) {
                    if ($bed->isEnabled()) {
                        ++$i;
                    }
                }

                if ($i > 1) {
                    $apartmentRoom->setPrivate(false);
                } else {
                    $apartmentRoom->setPrivate(true);
                }
            } else {
                $apartmentRoom->setPrivate(true);
            }

            $this->em->persist($apartmentRoom);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $apartmentRoom->getId();
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

            /** @var ApartmentRoomRepository $repo */
            $repo = $this->em->getRepository(ApartmentRoom::class);

            /** @var ApartmentRoom $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $id);

            if ($entity === null) {
                throw new ApartmentRoomNotFoundException();
            }

            $apartmentId = $params['apartment_id'] ?? 0;

            /** @var ApartmentRepository $apartmentRepo */
            $apartmentRepo = $this->em->getRepository(Apartment::class);

            /** @var Apartment $apartment */
            $apartment = $apartmentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Apartment::class), $apartmentId);

            if ($apartment === null) {
                throw new ApartmentNotFoundException();
            }

            $entity->setApartment($apartment);
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
                        $editedBeds[$bed['id']] = $bed;
                        $editedBedsIds[] = $bed['id'];
                    }
                }
            }

            if ($entity->getBeds() !== null) {
                /** @var ApartmentBed $existingBed */
                foreach ($entity->getBeds() as $existingBed) {
                    if (\in_array($existingBed->getId(), $editedBedsIds, false)) {
                        $existingBed->setNumber($editedBeds[$existingBed->getId()]['number']);
                        $existingBed->setEnabled($editedBeds[$existingBed->getId()]['enabled']);

                        $this->em->persist($existingBed);
                    } else {
                        /** @var ResidentAdmissionRepository $admissionRepo */
                        $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

                        $admission = $admissionRepo->getResidentByBed($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_APARTMENT, $existingBed->getId());

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
                    $newBed = new ApartmentBed();
                    $newBed->setNumber($bed['number']);
                    $newBed->setEnabled($bed['enabled']);
                    $newBed->setRoom($entity);
                    $entity->addBed($newBed);

                    $this->em->persist($newBed);
                }
            }

            $this->validate($entity, null, ['api_admin_apartment_room_edit']);

            if ($entity->getBeds() !== null) {
                $i = 0;

                /** @var ApartmentBed $bed */
                foreach ($entity->getBeds() as $bed) {
                    if ($bed->isEnabled()) {
                        ++$i;
                    }
                }

                if ($i > 1) {
                    $entity->setPrivate(false);
                } else {
                    $entity->setPrivate(true);
                }
            } else {
                $entity->setPrivate(true);
            }

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

            /** @var ApartmentRoomRepository $repo */
            $repo = $this->em->getRepository(ApartmentRoom::class);

            /** @var ApartmentRoom $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $id);

            if ($entity === null) {
                throw new ApartmentRoomNotFoundException();
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
                throw new ApartmentRoomNotFoundException();
            }

            /** @var ApartmentRoomRepository $repo */
            $repo = $this->em->getRepository(ApartmentRoom::class);

            $apartmentRooms = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $ids);

            if (empty($apartmentRooms)) {
                throw new ApartmentRoomNotFoundException();
            }

            /**
             * @var ApartmentRoom $apartmentRoom
             */
            foreach ($apartmentRooms as $apartmentRoom) {
                $this->em->remove($apartmentRoom);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param int $apartmentId
     * @return mixed
     */
    public function getLastNumber($apartmentId) {
        /** @var ApartmentRoomRepository $repo */
        $repo = $this->em->getRepository(ApartmentRoom::class);

        $max_number = $repo->getLastNumber($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $apartmentId);

        return $max_number ? $max_number['max_room_number'] : null;
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new ApartmentRoomNotFoundException();
        }

        /** @var ApartmentRoomRepository $repo */
        $repo = $this->em->getRepository(ApartmentRoom::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $ids);

        if (empty($entities)) {
            throw new ApartmentRoomNotFoundException();
        }

        return $this->getRelatedData(ApartmentRoom::class, $entities);
    }
}
