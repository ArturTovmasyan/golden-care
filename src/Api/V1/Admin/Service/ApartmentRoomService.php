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
use App\Entity\ContractAction;
use App\Model\ContractType;
use App\Repository\ApartmentBedRepository;
use App\Repository\ApartmentRepository;
use App\Repository\ApartmentRoomRepository;
use App\Repository\ContractActionRepository;
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

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $queryBuilder);
    }

    public function list($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $vacant = false;
        if (!empty($params) && !empty($params[0]['vacant']) && $params[0]['vacant'] === 1) {
            $vacant = true;
        }

        /** @var ApartmentRoomRepository $repo */
        $repo = $this->em->getRepository(ApartmentRoom::class);

        if (!empty($params) && !empty($params[0]['apartment_id'])) {
            $apartmentId = $params[0]['apartment_id'];

            $rooms = $repo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $apartmentId);
        } else {
            $rooms = $repo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class));
        }

        if (!empty($rooms)) {

            $roomIds = array_map(function(ApartmentRoom $item){return $item->getId();} , $rooms);

            /** @var ContractActionRepository $actionRepo */
            $actionRepo = $this->em->getRepository(ContractAction::class);

            /** @var ApartmentBedRepository $bedRepo */
            $bedRepo = $this->em->getRepository(ApartmentBed::class);

            $apartmentBeds = $bedRepo->getBedIdsByRooms($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentBed::class), $roomIds);
            $bedIds = [];
            if (\count($apartmentBeds)) {
                $bedIds = array_map(function($item){return $item['id'];} , $apartmentBeds);
            }

            if ($vacant) {
                $contractActions = $actionRepo->getBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ContractAction::class), ContractType::TYPE_APARTMENT, $bedIds);

                $occupancyBedIds = [];
                if (!empty($contractActions)) {
                    $occupancyBedIds = array_map(function($item){return $item['bedId'];} , $contractActions);
                }

                /** @var ApartmentRoom $room */
                foreach ($rooms as $room) {
                    $beds = $room->getBeds();

                    if (\count($beds)) {
                        /** @var ApartmentBed $bed */
                        foreach ($beds as $bed) {
                            if (\in_array($bed->getId(), $occupancyBedIds, false)) {
                                $room->removeBed($bed);
                            }
                        }
                    }
                }
            } else {
                $contractActions = $actionRepo->getResidentsByBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ContractAction::class), ContractType::TYPE_APARTMENT, $bedIds);

                $actions = [];
                if (!empty($contractActions)) {
                    foreach ($contractActions as $contractAction) {
                        $actions[$contractAction['bedId']] = $contractAction['action']->getContract()->getResident();
                    }
                }

                /** @var ApartmentRoom $room */
                foreach ($rooms as $room) {
                    $beds = $room->getBeds();

                    if (\count($beds)) {
                        /** @var ApartmentBed $bed */
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
     * @return ApartmentRoom|null|object
     */
    public function getById($id)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var ApartmentRoomRepository $repo */
        $repo = $this->em->getRepository(ApartmentRoom::class);

        $room = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $id);

        if ($room !== null) {
            /** @var ArrayCollection $beds */
            $beds = $room->getBeds();

            if ($beds !== null) {
                $ids = array_map(function(ApartmentBed $item){return $item->getId();} , $beds->toArray());

                /** @var ContractActionRepository $actionRepo */
                $actionRepo = $this->em->getRepository(ContractAction::class);

                $contractActions = $actionRepo->getResidentsByBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ContractAction::class), ContractType::TYPE_APARTMENT, $ids);

                $actions = [];
                if (!empty($contractActions)) {
                    foreach ($contractActions as $contractAction) {
                        $actions[$contractAction['bedId']] = $contractAction['action']->getContract()->getResident();
                    }
                }

                /** @var ApartmentBed $bed */
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

            $this->em->persist($apartmentRoom);
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

            /** @var ApartmentRoomRepository $repo */
            $repo = $this->em->getRepository(ApartmentRoom::class);

            /** @var ApartmentRoom $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $id);

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
                        /** @var ContractActionRepository $actionRepo */
                        $actionRepo = $this->em->getRepository(ContractAction::class);

                        $action = $actionRepo->getResidentByBed($currentSpace, $this->grantService->getCurrentUserEntityGrants(ContractAction::class), ContractType::TYPE_APARTMENT, $existingBed->getId());

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
                    $newBed = new ApartmentBed();
                    $newBed->setNumber($bed['number']);
                    $newBed->setEnabled($bed['enabled']);
                    $newBed->setRoom($entity);
                    $entity->addBed($newBed);

                    $this->em->persist($newBed);
                }
            }

            $this->validate($entity, null, ['api_admin_apartment_room_edit']);

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
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $id);

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

            $apartmentRooms = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $ids);

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
}
