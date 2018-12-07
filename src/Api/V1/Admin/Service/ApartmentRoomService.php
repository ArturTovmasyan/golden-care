<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ApartmentRoomNotFoundException;
use App\Api\V1\Common\Service\Exception\ApartmentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\Apartment;
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
        $this->em->getRepository(ApartmentRoom::class)->search($queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['apartment_id'])) {
            $apartmentId = $params[0]['apartment_id'];

            return $this->em->getRepository(ApartmentRoom::class)->findBy(['apartment' => $apartmentId]);
        }

        return $this->em->getRepository(ApartmentRoom::class)->findAll();
    }

    /**
     * @param $id
     * @return ApartmentRoom|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ApartmentRoom::class)->find($id);
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

            $apartment = null;

            if ($apartmentId && $apartmentId > 0) {
                /** @var Apartment $apartment */
                $apartment = $this->em->getRepository(Apartment::class)->find($apartmentId);


                if ($apartment === null) {
                    throw new ApartmentNotFoundException();
                }
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

            /** @var ApartmentRoom $entity */
            $entity = $this->em->getRepository(ApartmentRoom::class)->find($id);

            if ($entity === null) {
                throw new ApartmentRoomNotFoundException();
            }

            $apartmentId = $params['apartment_id'] ?? 0;

            $apartment = null;

            if ($apartmentId && $apartmentId > 0) {
                /** @var Apartment $apartment */
                $apartment = $this->em->getRepository(Apartment::class)->find($apartmentId);


                if ($apartment === null) {
                    throw new ApartmentNotFoundException();
                }
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
                        $editedBeds[$bed['id']] = $bed['number'];
                        $editedBedsIds[] = $bed['id'];
                    }
                }
            }

            if ($entity->getBeds() !== null) {
                /** @var ApartmentBed $existingBed */
                foreach ($entity->getBeds() as $existingBed) {
                    if (\in_array($existingBed->getId(), $editedBedsIds, false)) {
                        $existingBed->setNumber($editedBeds[$existingBed->getId()]);

                        $this->em->persist($existingBed);
                    } else {
                        $entity->removeBed($existingBed);
                        $this->em->remove($existingBed);
                    }
                }
            }

            if (!empty($addedBeds)) {
                foreach ($addedBeds as $bed) {
                    $newBed = new ApartmentBed();
                    $newBed->setNumber($bed['number']);
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
            $entity = $this->em->getRepository(ApartmentRoom::class)->find($id);

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

            $apartmentRooms = $this->em->getRepository(ApartmentRoom::class)->findByIds($ids);

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
