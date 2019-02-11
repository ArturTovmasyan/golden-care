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
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(ApartmentRoom::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $vacant = false;
        if (!empty($params) && !empty($params[0]['vacant']) && $params[0]['vacant'] === 1) {
            $vacant = true;
        }

        if (!empty($params) && !empty($params[0]['apartment_id'])) {
            $apartmentId = $params[0]['apartment_id'];

            $this->em->getRepository(ApartmentRoom::class)->getBy($currentSpace, $apartmentId);
        } else {
            $rooms = $this->em->getRepository(ApartmentRoom::class)->list($currentSpace);
        }

        if (!empty($rooms)) {

            $roomIds = array_map(function(ApartmentRoom $item){return $item->getId();} , $rooms);

            $facilityBeds = $this->em->getRepository(ApartmentBed::class)->getBedIdsByRooms($currentSpace, $roomIds);
            $bedIds = [];
            if (\count($facilityBeds)) {
                $bedIds = array_map(function($item){return $item['id'];} , $facilityBeds);
            }

            if ($vacant) {
                $contractActions = $this->em->getRepository(ContractAction::class)->getBeds($currentSpace, ContractType::TYPE_APARTMENT, $bedIds);

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
                $contractActions = $this->em->getRepository(ContractAction::class)->getResidentsByBeds($currentSpace, ContractType::TYPE_APARTMENT, $bedIds);

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

        $room = $this->em->getRepository(ApartmentRoom::class)->getOne($currentSpace, $id);

        if ($room !== null) {
            /** @var ArrayCollection $beds */
            $beds = $room->getBeds();

            if ($beds !== null) {
                $ids = array_map(function(ApartmentBed $item){return $item->getId();} , $beds->toArray());

                $contractActions = $this->em->getRepository(ContractAction::class)->getResidentsByBeds($currentSpace, ContractType::TYPE_APARTMENT, $ids);

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

            /** @var Apartment $apartment */
            $apartment = $this->em->getRepository(Apartment::class)->getOne($this->grantService->getCurrentSpace(), $apartmentId);

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

            /** @var ApartmentRoom $entity */
            $entity = $this->em->getRepository(ApartmentRoom::class)->getOne($currentSpace, $id);

            if ($entity === null) {
                throw new ApartmentRoomNotFoundException();
            }

            $apartmentId = $params['apartment_id'] ?? 0;

            /** @var Apartment $apartment */
            $apartment = $this->em->getRepository(Apartment::class)->getOne($currentSpace, $apartmentId);

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
                        $action = $this->em->getRepository(ContractAction::class)->getResidentByBed($currentSpace, ContractType::TYPE_APARTMENT, $existingBed->getId());

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ApartmentRoom $entity */
            $entity = $this->em->getRepository(ApartmentRoom::class)->getOne($this->grantService->getCurrentSpace(), $id);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            if (empty($ids)) {
                throw new ApartmentRoomNotFoundException();
            }

            $apartmentRooms = $this->em->getRepository(ApartmentRoom::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

            if (empty($apartmentRooms)) {
                throw new ApartmentRoomNotFoundException();
            }

            /**
             * @var ApartmentRoom $apartmentRoom
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($apartmentRooms as $apartmentRoom) {
                $this->em->remove($apartmentRoom);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ApartmentRoomNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
