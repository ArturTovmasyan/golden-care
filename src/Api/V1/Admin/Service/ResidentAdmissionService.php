<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ApartmentBedNotFoundException;
use App\Api\V1\Common\Service\Exception\CareLevelNotFoundException;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\DiningRoomNotFoundException;
use App\Api\V1\Common\Service\Exception\DiningRoomNotValidException;
use App\Api\V1\Common\Service\Exception\DuplicateResidentException;
use App\Api\V1\Common\Service\Exception\FacilityBedNotFoundException;
use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Api\V1\Common\Service\Exception\InvalidEffectiveDateException;
use App\Api\V1\Common\Service\Exception\LastResidentAdmissionNotFoundException;
use App\Api\V1\Common\Service\Exception\RegionCanNotHaveBedException;
use App\Api\V1\Common\Service\Exception\RegionNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentAdmissionNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentAdmissionOnlyAdmitException;
use App\Api\V1\Common\Service\Exception\ResidentAdmissionOnlyReadmitException;
use App\Api\V1\Common\Service\Exception\ResidentAdmissionTwoTimeARowException;
use App\Api\V1\Common\Service\Exception\ResidentAdmitOnlyOneTimeException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\CareLevel;
use App\Entity\CityStateZip;
use App\Entity\DiningRoom;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\Image;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentAdmission;
use App\Entity\ResidentPhone;
use App\Entity\ResidentRent;
use App\Model\AdmissionType;
use App\Model\GroupType;
use App\Model\ResidentState;
use App\Repository\ApartmentBedRepository;
use App\Repository\CareLevelRepository;
use App\Repository\CityStateZipRepository;
use App\Repository\DiningRoomRepository;
use App\Repository\FacilityBedRepository;
use App\Repository\ImageRepository;
use App\Repository\RegionRepository;
use App\Repository\ResidentAdmissionRepository;
use App\Repository\ResidentPhoneRepository;
use App\Repository\ResidentRentRepository;
use App\Repository\ResidentRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ResidentAdmissionService
 * @package App\Api\V1\Admin\Service
 */
class ResidentAdmissionService extends BaseService implements IGridService
{
    private const LIMIT = 30;

    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('ra.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentAdmission|null|object
     */
    public function getById($id)
    {
        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getResidentLastAdmission($id)
    {
        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        return $repo->getLastAction($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $id);
    }

    /**
     * @param $type
     * @param $ids
     * @return mixed
     */
    public function getResidentsByBedIds($type, $ids)
    {
        $data = [];

        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        $residents = $repo->getResidentsByBedIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $type, $ids);
        $residents = array_column($residents, 'fullName', 'id');

        foreach ($ids as $id) {
            if (array_key_exists((int)$id, $residents)) {
                $data[$id][] = $residents[(int)$id];
            } else {
                $data[$id][] = null;
            }
        }

        return $data;
    }

    /**
     * @param $id
     * @return ResidentAdmission|null|object
     */
    public function getActiveByResidentId($id)
    {
        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        return $repo->getActiveByResident($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $id);
    }

    /**
     * @param $id
     * @return ResidentAdmission|null|object
     */
    public function getActiveWithFacilityRoomBaseRateByResidentId($id)
    {
        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        return $repo->getActiveWithFacilityRoomBaseRateByResident($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $id);
    }

    /**
     * @param $type
     * @param $typeId
     * @param $resident
     * @param $room
     * @return array|null
     */
    public function getActiveResidents($type, $typeId, $resident, $room): ?array
    {
        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        $isFilter = true;
        if ($type === null && $typeId === null && $resident === null && $room === null) {
            $isFilter = false;
        }

        $data = [
            [
                'groupType' => GroupType::TYPE_FACILITY,
                'entityClass' => Facility::class,
                'title' => 'facility'
            ],
            [
                'groupType' => GroupType::TYPE_APARTMENT,
                'entityClass' => Apartment::class,
                'title' => 'apartment'
            ],
            [
                'groupType' => GroupType::TYPE_REGION,
                'entityClass' => Region::class,
                'title' => 'region'
            ]
        ];

        if ($type !== null) {
            foreach ($data as $key => $datum) {
                if ($datum['groupType'] !== $type) {
                    unset($data[$key]);
                }
            }
            $data = array_values($data);
        }

        $result = [];
        foreach ($data as $strategy) {
            $groupRepo = $this->em->getRepository($strategy['entityClass']);

            $groups = [];
            $groupIds = null;
            if ($typeId !== null) {
                $groupList = $groupRepo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants($strategy['entityClass']), $typeId);
            } else {
                $groupList = $groupRepo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants($strategy['entityClass']));
            }

            if (!empty($groupList)) {
                $groupIds = array_map(static function ($item) {
                    return $item->getId();
                }, $groupList);
                $groupArray = array_map(static function ($item) {
                    return ['id' => $item->getId(), 'name' => $item->getName()];
                }, $groupList);

                $groupResidents = $repo->getMainActiveResidents($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $strategy['groupType'], $groupIds, $resident, $room, $isFilter);

                $images = [];
                $imageS3Uris = [];
                if (!empty($groupResidents)) {
                    $residentIds = array_map(static function ($item) {
                        return $item['id'];
                    }, $groupResidents);

                    /** @var ImageRepository $imageRepo */
                    $imageRepo = $this->em->getRepository(Image::class);

                    $images = $imageRepo->findByResidentIds($residentIds);
                    $imageS3Uris = array_column($images, 's3Uri', 'id');
                    $images = array_column($images, 'id', 'id');
                }

                foreach ($groupArray as $group) {
                    $currentGroup = [
                        'id' => $group['id'],
                        'name' => $group['name'],
                        'residents' => []
                    ];
                    $i = 0;
                    foreach ($groupResidents as $groupResident) {
                        if ($groupResident['type_id'] === $group['id']) {

                            if (array_key_exists($groupResident['id'], $images)) {
                                $groupResident['photo'] = $imageS3Uris[$groupResident['id']];
                            } else {
                                $groupResident['photo'] = null;
                            }

                            ++$i;
                            $currentGroup['residents'][] = $groupResident;
                        }
                        if ($i === self::LIMIT) {
                            break;
                        }
                    }

                    if (!empty($currentGroup['residents'])) {
                        $groups[] = $currentGroup;
                    }
                }
            }

            $result[$strategy['title']] = $groups;
        }

        return $result;
    }

    /**
     * @param $state
     * @param $page
     * @param $perPage
     * @param $type
     * @param $typeId
     * @param $resident
     * @param $room
     * @return array|null
     */
    public function getPerPageResidents($state, $page, $perPage, $type, $typeId, $resident, $room): ?array
    {
        $isFilter = true;
        if ($type === null && $typeId === null && $resident === null && $room === null) {
            $isFilter = false;
        }

        $currentSpace = $this->grantService->getCurrentSpace();

        $result = [];
        $residents = [];
        $total = 0;
        $inactive = false;
        if ($state === ResidentState::TYPE_NO_ADMISSION) {
            /** @var ResidentRepository $repo */
            $repo = $this->em->getRepository(Resident::class);

            $residents = $repo->getPerPageNoAdmissionResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $page, $perPage, $resident);
            $total = $repo->getCountNoAdmissionResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class));
        } elseif ($state === ResidentState::TYPE_ACTIVE) {
            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            $residents = $repo->getPerPageActiveOrInactiveResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $this->getNotGrantResidentIds(), $page, $perPage, $inactive, $type, $typeId, $resident, $room, $isFilter);
            $total = $repo->getCountActiveOrInactiveResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $this->getNotGrantResidentIds(), null, $inactive, $type, $typeId);
        } elseif ($state === ResidentState::TYPE_INACTIVE) {
            $inactive = true;

            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            $residents = $repo->getPerPageActiveOrInactiveResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $this->getNotGrantResidentIds(), $page, $perPage, $inactive, $type, $typeId, $resident, $room, $isFilter);
            $total = $repo->getCountActiveOrInactiveResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $this->getNotGrantResidentIds(), null, $inactive, $type, $typeId);
        }

        $finalResidents = [];
        if (!empty($residents)) {
            $residentIds = array_map(static function ($item) {
                return $item['id'];
            }, $residents);

            /** @var ImageRepository $imageRepo */
            $imageRepo = $this->em->getRepository(Image::class);

            $images = $imageRepo->findByResidentIds($residentIds);
            $imageS3Uris = array_column($images, 's3Uri', 'id');
            $images = array_column($images, 'id', 'id');

            foreach ($residents as $item) {
                if (array_key_exists($item['id'], $images)) {
                    $item['photo'] = $imageS3Uris[$item['id']];
                } else {
                    $item['photo'] = null;
                }

                $finalResidents[] = $item;
            }

            $result = [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'data' => $finalResidents
            ];
        }

        return $result;
    }

    /**
     * @param RouterInterface $router
     * @param $state
     * @param $page
     * @param $perPage
     * @param $date
     * @param $type
     * @param $typeId
     * @return array
     */
    public function getMobilePerPageResidents(RouterInterface $router, $state, $page, $perPage, $date, $type, $typeId): ?array
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $result = [];
        $residents = [];
        $total = 1;
        $inactive = false;
        if ($state === ResidentState::TYPE_NO_ADMISSION) {
            /** @var ResidentRepository $repo */
            $repo = $this->em->getRepository(Resident::class);

            $residents = $repo->getMobilePerPageNoAdmissionResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $page, $perPage, $date);
            $total = $repo->getCountNoAdmissionResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $date);
        } elseif ($state === ResidentState::TYPE_ACTIVE) {
            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            $residents = $repo->getMobilePerPageActiveOrInactiveResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $this->getNotGrantResidentIds(), $page, $perPage, $date, $inactive, $type, $typeId);
            $total = $repo->getCountActiveOrInactiveResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $this->getNotGrantResidentIds(), $date, $inactive, $type, $typeId);
        } elseif ($state === ResidentState::TYPE_INACTIVE) {
            $inactive = true;

            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            $residents = $repo->getMobilePerPageActiveOrInactiveResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $this->getNotGrantResidentIds(), $page, $perPage, $date, $inactive, $type, $typeId);
            $total = $repo->getCountActiveOrInactiveResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $this->getNotGrantResidentIds(), $date, $inactive, $type, $typeId);
        }

        if ($total > 0 && $perPage > 0) {
            $total = (int)ceil($total/$perPage);
        }

        $finalResidents = [];
        if (!empty($residents)) {
            $residentIds = array_map(static function ($item) {
                return $item['id'];
            }, $residents);

            /** @var ImageRepository $imageRepo */
            $imageRepo = $this->em->getRepository(Image::class);

            $images = $imageRepo->findByResidentIds($residentIds);
            $images = array_column($images, 'id', 'id');

            /** @var ResidentPhoneRepository $phoneRepo */
            $phoneRepo = $this->em->getRepository(ResidentPhone::class);
            $phones = $phoneRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhone::class), $residentIds);

            foreach ($residents as $resident) {
                $resident['phones'] = null;
                $resident['birthday'] = $resident['birthday']->format('Y-m-d H:i:s');
                $resident['updated_at'] = $resident['updated_at'] !== null ? $resident['updated_at']->format('Y-m-d H:i:s') : $resident['updated_at'];

                if (array_key_exists('effective_date', $resident)) {
                    $resident['effective_date'] = $resident['effective_date']->format('Y-m-d H:i:s');
                }

                if (array_key_exists($resident['id'], $images)) {
                    $resident['photo'] = $router->generate('api_admin_resident_image_download', ['id' => $resident['id']], UrlGeneratorInterface::ABSOLUTE_URL).'?mobile';
                } else {
                    $resident['photo'] = null;
                }

                foreach ($phones as $phone) {
                    if ($phone['rId'] === $resident['id']) {
                        $resident['phones'][] = $phone;
                    }
                }

                $finalResidents[] = $resident;
            }

            $result = [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'data' => $finalResidents
            ];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getCountActiveResidents(): ?array
    {
        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        $data = [
            [
                'groupType' => GroupType::TYPE_FACILITY,
                'entityClass' => Facility::class,
                'title' => 'facility'
            ],
            [
                'groupType' => GroupType::TYPE_APARTMENT,
                'entityClass' => Apartment::class,
                'title' => 'apartment'
            ],
            [
                'groupType' => GroupType::TYPE_REGION,
                'entityClass' => Region::class,
                'title' => 'region'
            ]
        ];

        $result = [];
        foreach ($data as $strategy) {
            $groupRepo = $this->em->getRepository($strategy['entityClass']);

            $groups = [];
            $groupIds = null;
            $groupList = $groupRepo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants($strategy['entityClass']));
            if (!empty($groupList)) {
                $groupIds = array_map(static function ($item) {
                    return $item->getId();
                }, $groupList);
                $groupArray = array_map(static function ($item) {
                    return ['id' => $item->getId(), 'name' => $item->getName()];
                }, $groupList);

                $groupResidents = $repo->getActiveResidents($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $strategy['groupType'], $groupIds);

                foreach ($groupArray as $group) {
                    $currentGroup = [
                        'id' => $group['id'],
                        'name' => $group['name'],
                        'count' => 0
                    ];
                    $i = 0;
                    foreach ($groupResidents as $groupResident) {
                        if ($groupResident['type_id'] === $group['id']) {
                            ++$i;
                            $currentGroup['count'] = $i;
                        }
                    }

                    $groups[] = $currentGroup;
                }
            }

            $result[$strategy['title']] = $groups;
        }

        return $result;
    }

    /**
     * @param $state
     * @return Resident|array|null|object
     */
    public function getStateResidents($state)
    {
        if (!\in_array($state, ResidentState::getTypes(), false)) {
            throw new InvalidParameterException('state');
        }

        $result = [];
        $inactive = false;
        if ($state === ResidentState::TYPE_ACTIVE) {
            $result = $this->getActiveOrInactiveResidents($inactive);
        }

        if ($state === ResidentState::TYPE_INACTIVE) {
            $inactive = true;
            $result = $this->getActiveOrInactiveResidents($inactive);
        }

        if ($state === ResidentState::TYPE_NO_ADMISSION) {
            $result = $this->getNoAdmissionResidents();
        }

        return $result;
    }

    /**
     * @param $inactive
     * @return array
     */
    public function getActiveOrInactiveResidents($inactive): ?array
    {
        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        $data = [
            [
                'groupType' => GroupType::TYPE_FACILITY,
                'entityClass' => Facility::class,
                'title' => 'facility'
            ],
            [
                'groupType' => GroupType::TYPE_APARTMENT,
                'entityClass' => Apartment::class,
                'title' => 'apartment'
            ],
            [
                'groupType' => GroupType::TYPE_REGION,
                'entityClass' => Region::class,
                'title' => 'region'
            ]
        ];

        $result = [];
        foreach ($data as $strategy) {
            $groupRepo = $this->em->getRepository($strategy['entityClass']);

            $groupIds = null;
            $groupList = $groupRepo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants($strategy['entityClass']));
            if (!empty($groupList)) {
                $groupIds = array_map(static function ($item) {
                    return $item->getId();
                }, $groupList);
                $groupArray = array_map(static function ($item) {
                    return ['id' => $item->getId(), 'name' => $item->getName()];
                }, $groupList);

                if ($inactive) {
                    $groupResidents = $repo->getInactiveResidents($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $strategy['groupType'], $groupIds);
                } else {
                    $groupResidents = $repo->getActiveResidents($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $strategy['groupType'], $groupIds);
                }

                foreach ($groupArray as $group) {
                    foreach ($groupResidents as $groupResident) {
                        if ($groupResident['type_id'] === $group['id']) {
                            $groupResident['type_name'] = $group['name'];

                            $result[] = $groupResident;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param $type
     * @param $id
     * @return ResidentAdmission|null|object
     */
    public function getActiveResidentsByStrategy($type, $id)
    {
        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        return $repo->getActiveResidentsByStrategy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $type, $id);
    }

    /**
     * @param $type
     * @param $id
     * @return ResidentAdmission|null|object
     */
    public function getInactiveResidentsByStrategy($type, $id)
    {
        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        return $repo->getInactiveResidentsByStrategy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $type, $id);
    }

    /**
     * @return Resident|null|object
     */
    public function getNoAdmissionResidents()
    {
        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        return $repo->getNoAdmissionResidents($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class));
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $admissionType = isset($params['admission_type']) ? (int)$params['admission_type'] : 0;

            /** @var ResidentAdmissionRepository $admissionRepo */
            $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

            /** @var ResidentAdmission $lastAction */
            $lastAction = $admissionRepo->getLastAction($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $residentId);

            $admitTypesArray = [
                AdmissionType::LONG_ADMIT,
                AdmissionType::SHORT_ADMIT,
            ];

            if ($lastAction === null && !\in_array($admissionType, $admitTypesArray, false)) {
                throw new ResidentAdmissionOnlyAdmitException();
            }

            $entity = new ResidentAdmission();
            $entity->setResident($resident);

            if ($admissionType === AdmissionType::TEMPORARY_DISCHARGE || $admissionType === AdmissionType::PENDING_DISCHARGE || $admissionType === AdmissionType::DISCHARGE) {
                if ($lastAction === null) {
                    throw new LastResidentAdmissionNotFoundException();
                }

                $lastActionAdmissionType = $lastAction->getAdmissionType();

                if ($lastActionAdmissionType === AdmissionType::DISCHARGE) {
                    throw new ResidentAdmissionOnlyReadmitException();
                }

                if (($lastActionAdmissionType === $admissionType) === AdmissionType::TEMPORARY_DISCHARGE || ($lastActionAdmissionType === $admissionType) === AdmissionType::PENDING_DISCHARGE) {
                    throw new ResidentAdmissionTwoTimeARowException();
                }

                $entity->setGroupType($lastAction->getGroupType());
            } else {
                /** @var ResidentAdmission $admitAction */
                $admitAction = $admissionRepo->getOneAdmitAction($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $residentId);

                if ($lastAction !== null && $admitAction !== null && \in_array($admissionType, $admitTypesArray, false)) {
                    throw new ResidentAdmitOnlyOneTimeException();
                }

                $type = $params['group_type'] ? (int)$params['group_type'] : 0;
                $entity->setGroupType($type);
            }

            $entity->setAdmissionType($admissionType);
            $entity->setNotes($params['notes']);

            /** @var ResidentRentRepository $rentRepo */
            $rentRepo = $this->em->getRepository(ResidentRent::class);

            /** @var ResidentRent $lastRent */
            $lastRent = $rentRepo->getLastRent($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $residentId);

            $date = $params['date'];
            if (!empty($date)) {
                $date = new \DateTime($params['date']);

                $now = new \DateTime('now');
                $date->setTime($now->format('H'), $now->format('i'), $now->format('s'));

                $entity->setDate($date);

                if ($lastAction !== null && $date <= $lastAction->getStart()) {
                    throw new InvalidEffectiveDateException();
                }

                if ($admissionType === AdmissionType::DISCHARGE && $lastRent !== null && $date <= $lastRent->getStart()) {
                    throw new InvalidEffectiveDateException();
                }

                $entity->setStart($date);
            } else {
                $entity->setDate(null);
                $entity->setStart(null);
            }

            if ($lastAction !== null) {
                $lastAction->setEnd($entity->getStart());

                $this->em->persist($lastAction);
            }

            if ($admissionType === AdmissionType::DISCHARGE && $lastRent !== null) {
                $lastRent->setEnd($entity->getStart());

                $this->em->persist($lastRent);
            }

            $addMode = true;
            switch ($entity->getGroupType()) {
                case GroupType::TYPE_FACILITY:
                    $validationGroup = 'api_admin_facility_add';
                    $entity = $this->saveAsFacility($entity, $params, $admissionType, $lastAction, $addMode);
                    break;
                case GroupType::TYPE_APARTMENT:
                    $validationGroup = 'api_admin_apartment_add';
                    $entity = $this->saveAsApartment($entity, $params, $admissionType, $lastAction, $addMode);
                    break;
                case GroupType::TYPE_REGION:
                    $validationGroup = 'api_admin_region_add';
                    $entity = $this->saveAsRegion($entity, $params, $admissionType, $lastAction, $addMode);
                    break;
                default:
                    throw new IncorrectStrategyTypeException();
            }

            if ($admissionType === AdmissionType::TEMPORARY_DISCHARGE || $admissionType === AdmissionType::PENDING_DISCHARGE || $admissionType === AdmissionType::DISCHARGE) {
                $validationGroup = $entity->getGroupType() === GroupType::TYPE_APARTMENT ? 'api_admin_apartment_discharge_add' : 'api_admin_discharge_add';
            }

            $this->validate($entity, null, [$validationGroup]);
            $this->em->persist($entity);

            //update resident for mobile
            $resident->setUpdatedAt(new \DateTime('now'));
            $this->em->persist($resident);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $entity->getId();
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
    public function edit($id, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            /** @var ResidentAdmission $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $id);

            if ($entity === null) {
                throw new ResidentAdmissionNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            /** @var ResidentAdmissionRepository $admissionRepo */
            $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

            /** @var ResidentAdmission $lastAction */
            $lastAction = $admissionRepo->getLastAction($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $params['resident_id']);

            $admissionType = $entity->getAdmissionType();

            $entity->setResident($resident);
            $entity->setNotes($params['notes']);

            $date = $params['date'];
            if (!empty($date)) {
                $date = new \DateTime($params['date']);

                $now = new \DateTime('now');
                $date->setTime($now->format('H'), $now->format('i'), $now->format('s'));

                $entity->setDate($date);

                /** @var ResidentAdmission|null $previousAdmission */
                $previousAdmission = null;
                /** @var ResidentAdmission|null $nextAdmission */
                $nextAdmission = null;

                $admissions = $admissionRepo->getByOrderedStartDate($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $params['resident_id']);

                if (\count($admissions) <= 1) {
                    $previousAdmission = null;
                    $nextAdmission = null;
                } else {
                    $length = \count($admissions);

                    /**
                     * @var  $key
                     * @var ResidentAdmission $admission
                     */
                    foreach ($admissions as $key => $admission) {
                        if ($admission->getId() === $entity->getId()) {
                            if ($key >= $length - 1) {
                                $previousAdmission = $admissions[$key - 1];
                                $nextAdmission = null;
                            } else {
                                $nextAdmission = $admissions[$key + 1];
                                if ($key === 0) {
                                    $previousAdmission = null;
                                } else {
                                    $previousAdmission = $admissions[$key - 1];
                                }
                            }
                        }
                    }
                }

                if ($previousAdmission !== null && $date <= $previousAdmission->getStart()) {
                    throw new InvalidEffectiveDateException();
                }

                if ($nextAdmission !== null && $date >= $nextAdmission->getStart()) {
                    throw new InvalidEffectiveDateException();
                }

                $entity->setStart($date);

                if ($previousAdmission !== null) {
                    $previousAdmission->setEnd($entity->getStart());

                    $this->em->persist($previousAdmission);
                }
            } else {
                $entity->setDate(null);
                $entity->setStart(null);
            }

            $addMode = false;
            switch ($entity->getGroupType()) {
                case GroupType::TYPE_FACILITY:
                    $validationGroup = 'api_admin_facility_edit';
                    $entity = $this->saveAsFacility($entity, $params, $admissionType, $lastAction, $addMode);
                    break;
                case GroupType::TYPE_APARTMENT:
                    $validationGroup = 'api_admin_apartment_edit';
                    $entity = $this->saveAsApartment($entity, $params, $admissionType, $lastAction, $addMode);
                    break;
                case GroupType::TYPE_REGION:
                    $validationGroup = 'api_admin_region_edit';
                    $entity = $this->saveAsRegion($entity, $params, $admissionType, $lastAction, $addMode);
                    break;
                default:
                    throw new IncorrectStrategyTypeException();
            }

            if ($admissionType === AdmissionType::TEMPORARY_DISCHARGE || $admissionType === AdmissionType::PENDING_DISCHARGE || $admissionType === AdmissionType::DISCHARGE) {
                $validationGroup = $entity->getGroupType() === GroupType::TYPE_APARTMENT ? 'api_admin_apartment_discharge_edit' : 'api_admin_discharge_edit';
            }

            $this->validate($entity, null, [$validationGroup]);
            $this->em->persist($entity);

            //update resident for mobile
            $resident->setUpdatedAt(new \DateTime('now'));
            $this->em->persist($resident);

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
     * @throws \Throwable
     */
    public function move($id, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $id);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $type = !empty($params['group_type']) ? (int)$params['group_type'] : 0;

            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            //assignment mode
            if (!empty($params['move_id'])) {

                /** @var ResidentAdmission $admission */
                $admission = $repo->getDataByResident($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $type, $id);

                if ($admission === null) {
                    throw new ResidentAdmissionNotFoundException();
                }

                $moveId = (int)$params['move_id'];

                switch ($type) {
                    case GroupType::TYPE_FACILITY:
                        /** @var FacilityBedRepository $facilityBedRepo */
                        $facilityBedRepo = $this->em->getRepository(FacilityBed::class);

                        /** @var FacilityBed $bed */
                        $bed = $facilityBedRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityBed::class), $moveId);

                        if ($bed === null) {
                            throw new FacilityBedNotFoundException();
                        }

                        $now = new \DateTime('now');

                        $entity = new ResidentAdmission();
                        $entity->setResident($admission->getResident());
                        $entity->setGroupType($admission->getGroupType());
                        $entity->setAdmissionType(AdmissionType::ROOM_CHANGE);
                        $entity->setStart($now);
                        $entity->setDate($now);
                        $entity->setFacilityBed($bed);
                        $entity->setDiningRoom($admission->getDiningRoom());
                        $entity->setDnr($admission->isDnr());
                        $entity->setPolst($admission->isPolst());
                        $entity->setAmbulatory($admission->isAmbulatory());
                        $entity->setCareGroup($admission->getCareGroup());
                        $entity->setCareLevel($admission->getCareLevel());
                        $entity->setNotes($admission->getNotes());

                        $this->em->persist($entity);

                        $admission->setEnd($now);
                        $this->em->persist($admission);

                        break;
                    case GroupType::TYPE_APARTMENT:
                        /** @var ApartmentBedRepository $apartmentBedRepo */
                        $apartmentBedRepo = $this->em->getRepository(ApartmentBed::class);

                        /** @var ApartmentBed $bed */
                        $bed = $apartmentBedRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentBed::class), $moveId);

                        if ($bed === null) {
                            throw new ApartmentBedNotFoundException();
                        }

                        $now = new \DateTime('now');

                        $entity = new ResidentAdmission();
                        $entity->setResident($admission->getResident());
                        $entity->setGroupType($admission->getGroupType());
                        $entity->setAdmissionType(AdmissionType::ROOM_CHANGE);
                        $entity->setStart($now);
                        $entity->setDate($now);
                        $entity->setApartmentBed($bed);
                        $entity->setNotes($admission->getNotes());

                        $this->em->persist($entity);

                        $admission->setEnd($now);
                        $this->em->persist($admission);

                        break;
                    case GroupType::TYPE_REGION:
                        throw new RegionCanNotHaveBedException();

                        break;
                    default:
                        throw new IncorrectStrategyTypeException();
                }

                //update resident for mobile
                $resident->setUpdatedAt(new \DateTime('now'));
                $this->em->persist($resident);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $params
     * @throws \Throwable
     */
    public function swap(array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $firstId = $params['first_id'] ?? 0;
            $secondId = $params['second_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $firstResident */
            $firstResident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $firstId);

            /** @var Resident $secondResident */
            $secondResident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $secondId);

            if ($firstResident === null || $secondResident === null) {
                throw new ResidentNotFoundException();
            }

            if ($firstResident->getId() === $secondResident->getId()) {
                throw new DuplicateResidentException();
            }

            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            /** @var ResidentAdmission $firstAdmission */
            $firstAdmission = $repo->getDataByResident($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $firstId);

            /** @var ResidentAdmission $secondAdmission */
            $secondAdmission = $repo->getDataByResident($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $secondId);

            if ($firstAdmission === null || $secondAdmission === null) {
                throw new ResidentAdmissionNotFoundException();
            }

            $firstEntity = new ResidentAdmission();
            $secondEntity = new ResidentAdmission();

            $date = $params['date'];
            if (!empty($date)) {
                $date = new \DateTime($params['date']);

                $now = new \DateTime('now');
                $date->setTime($now->format('H'), $now->format('i'), $now->format('s'));

                $firstEntity->setDate($date);
                $secondEntity->setDate($date);

                $dateFormatted = $date->format('Y-m-d');
                if (($firstAdmission->getStart() !== null && $dateFormatted <= $firstAdmission->getStart()->format('Y-m-d')) || ($secondAdmission->getStart() !== null && $dateFormatted <= $secondAdmission->getStart()->format('Y-m-d'))) {
                    throw new InvalidEffectiveDateException();
                }

                $firstEntity->setStart($date);
                $secondEntity->setStart($date);

                $firstAdmission->setEnd($date);
                $secondAdmission->setEnd($date);

                $this->em->persist($firstAdmission);
                $this->em->persist($secondAdmission);
            } else {
                $firstEntity->setDate(null);
                $firstEntity->setStart(null);

                $secondEntity->setDate(null);
                $secondEntity->setStart(null);
            }

            $firstEntity->setFacilityBed($secondAdmission->getFacilityBed());
            $firstEntity->setResident($firstAdmission->getResident());
            $firstEntity->setGroupType($firstAdmission->getGroupType());
            $firstEntity->setAdmissionType(AdmissionType::ROOM_CHANGE);
            $firstEntity->setDiningRoom($firstAdmission->getDiningRoom());
            $firstEntity->setDnr($firstAdmission->isDnr());
            $firstEntity->setPolst($firstAdmission->isPolst());
            $firstEntity->setAmbulatory($firstAdmission->isAmbulatory());
            $firstEntity->setCareGroup($firstAdmission->getCareGroup());
            $firstEntity->setCareLevel($firstAdmission->getCareLevel());
            $firstEntity->setNotes('');

            $this->em->persist($firstEntity);

            $secondEntity->setFacilityBed($firstAdmission->getFacilityBed());
            $secondEntity->setResident($secondAdmission->getResident());
            $secondEntity->setGroupType($secondAdmission->getGroupType());
            $secondEntity->setAdmissionType(AdmissionType::ROOM_CHANGE);
            $secondEntity->setDiningRoom($secondAdmission->getDiningRoom());
            $secondEntity->setDnr($secondAdmission->isDnr());
            $secondEntity->setPolst($secondAdmission->isPolst());
            $secondEntity->setAmbulatory($secondAdmission->isAmbulatory());
            $secondEntity->setCareGroup($secondAdmission->getCareGroup());
            $secondEntity->setCareLevel($secondAdmission->getCareLevel());
            $secondEntity->setNotes('');

            $this->em->persist($secondEntity);

            //update resident for mobile
            $firstResident->setUpdatedAt(new \DateTime('now'));
            $this->em->persist($firstResident);

            $secondResident->setUpdatedAt(new \DateTime('now'));
            $this->em->persist($secondResident);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param ResidentAdmission $entity
     * @param array $params
     * @param int $admissionType
     * @param ResidentAdmission|null $lastAction
     * @param $addMode
     * @return ResidentAdmission
     */
    public function saveAsFacility(ResidentAdmission $entity, array $params, int $admissionType, ResidentAdmission $lastAction = null, $addMode)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        if ($addMode && $lastAction !== null && ($admissionType === AdmissionType::TEMPORARY_DISCHARGE || $admissionType === AdmissionType::PENDING_DISCHARGE || $admissionType === AdmissionType::DISCHARGE)) {
            $entity->setDiningRoom($lastAction->getDiningRoom());
            $entity->setFacilityBed($lastAction->getFacilityBed());
            $entity->setDnr($lastAction->isDnr());
            $entity->setPolst($lastAction->isPolst());
            $entity->setAmbulatory($lastAction->isAmbulatory());
            $entity->setCareGroup($lastAction->getCareGroup());
            $entity->setCareLevel($lastAction->getCareLevel());
        }

        if ($admissionType !== AdmissionType::TEMPORARY_DISCHARGE && $admissionType !== AdmissionType::PENDING_DISCHARGE && $admissionType !== AdmissionType::DISCHARGE) {
            /** @var DiningRoomRepository $diningRoomRepo */
            $diningRoomRepo = $this->em->getRepository(DiningRoom::class);

            /** @var DiningRoom $diningRoom */
            $diningRoom = $diningRoomRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(DiningRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $params['dining_room_id']);

            /** @var FacilityBedRepository $facilityBedRepo */
            $facilityBedRepo = $this->em->getRepository(FacilityBed::class);

            /** @var FacilityBed $facilityBed */
            $facilityBed = $facilityBedRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityBed::class), $params['facility_bed_id']);

            /** @var CareLevelRepository $careLevelRepo */
            $careLevelRepo = $this->em->getRepository(CareLevel::class);

            /** @var CareLevel $careLevel */
            $careLevel = $careLevelRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $params['care_level_id']);

            if ($diningRoom === null) {
                throw new DiningRoomNotFoundException();
            }

            if ($facilityBed === null) {
                throw new FacilityBedNotFoundException();
            }

            if ($careLevel === null) {
                throw new CareLevelNotFoundException();
            }

            $roomFacility = $facilityBed->getRoom() ? $facilityBed->getRoom()->getFacility() : null;
            $roomFacilityId = 0;
            if ($roomFacility !== null) {
                $roomFacilityId = $roomFacility->getId();
            }

            $diningRoomFacility = $diningRoom->getFacility();
            $diningRoomFacilityId = 0;
            if ($diningRoomFacility !== null) {
                $diningRoomFacilityId = $diningRoomFacility->getId();
            }

            if ($roomFacilityId > 0 && $diningRoomFacilityId > 0 && $diningRoomFacilityId !== $roomFacilityId) {
                throw new DiningRoomNotValidException();
            }

            $careGroup = $params['care_group'] ? (int)$params['care_group'] : 0;

            $entity->setDiningRoom($diningRoom);
            $entity->setFacilityBed($facilityBed);
            $entity->setDnr($params['dnr'] ?? false);
            $entity->setPolst($params['polst'] ?? false);
            $entity->setAmbulatory($params['ambulatory'] ?? false);
            $entity->setCareGroup($careGroup);
            $entity->setCareLevel($careLevel);
        }

        return $entity;
    }

    /**
     * @param ResidentAdmission $entity
     * @param array $params
     * @param int $admissionType
     * @param ResidentAdmission|null $lastAction
     * @param $addMode
     * @return ResidentAdmission
     */
    private function saveAsApartment(ResidentAdmission $entity, array $params, int $admissionType, ResidentAdmission $lastAction = null, $addMode)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        if ($addMode && $lastAction !== null && ($admissionType === AdmissionType::TEMPORARY_DISCHARGE || $admissionType === AdmissionType::PENDING_DISCHARGE || $admissionType === AdmissionType::DISCHARGE)) {
            $entity->setApartmentBed($lastAction->getApartmentBed());
        }

        if ($admissionType !== AdmissionType::TEMPORARY_DISCHARGE && $admissionType !== AdmissionType::PENDING_DISCHARGE && $admissionType !== AdmissionType::DISCHARGE) {
            /** @var ApartmentBedRepository $apartmentBedRepo */
            $apartmentBedRepo = $this->em->getRepository(ApartmentBed::class);

            /** @var ApartmentBed $apartmentBed */
            $apartmentBed = $apartmentBedRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentBed::class), $params['apartment_bed_id']);

            if ($apartmentBed === null) {
                throw new ApartmentBedNotFoundException();
            }

            $entity->setApartmentBed($apartmentBed);
        }

        return $entity;
    }

    /**
     * @param ResidentAdmission $entity
     * @param array $params
     * @param int $admissionType
     * @param ResidentAdmission|null $lastAction
     * @param $addMode
     * @return ResidentAdmission
     */
    private function saveAsRegion(ResidentAdmission $entity, array $params, int $admissionType, ResidentAdmission $lastAction = null, $addMode)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        if ($addMode && $lastAction !== null && ($admissionType === AdmissionType::TEMPORARY_DISCHARGE || $admissionType === AdmissionType::PENDING_DISCHARGE || $admissionType === AdmissionType::DISCHARGE)) {
            $entity->setRegion($lastAction->getRegion());
            $entity->setCsz($lastAction->getCsz());
            $entity->setAddress($lastAction->getAddress());
            $entity->setDnr($lastAction->isDnr());
            $entity->setPolst($lastAction->isPolst());
            $entity->setAmbulatory($lastAction->isAmbulatory());
            $entity->setCareGroup($lastAction->getCareGroup());
            $entity->setCareLevel($lastAction->getCareLevel());
        }

        if ($admissionType !== AdmissionType::TEMPORARY_DISCHARGE && $admissionType !== AdmissionType::PENDING_DISCHARGE && $admissionType !== AdmissionType::DISCHARGE) {
            /** @var RegionRepository $regionRepo */
            $regionRepo = $this->em->getRepository(Region::class);

            /** @var Region $region */
            $region = $regionRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Region::class), $params['region_id']);

            /** @var CityStateZipRepository $cszRepo */
            $cszRepo = $this->em->getRepository(CityStateZip::class);

            /** @var CityStateZip $csz */
            $csz = $cszRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $params['csz_id']);

            /** @var CareLevelRepository $careLevelRepo */
            $careLevelRepo = $this->em->getRepository(CareLevel::class);

            /** @var CareLevel $careLevel */
            $careLevel = $careLevelRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $params['care_level_id']);

            if ($region === null) {
                throw new RegionNotFoundException();
            }

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            if ($careLevel === null) {
                throw new CareLevelNotFoundException();
            }

            $careGroup = $params['care_group'] ? (int)$params['care_group'] : 0;

            $entity->setRegion($region);
            $entity->setCsz($csz);
            $entity->setAddress($params['address']);
            $entity->setDnr($params['dnr'] ?? false);
            $entity->setPolst($params['polst'] ?? false);
            $entity->setAmbulatory($params['ambulatory'] ?? false);
            $entity->setCareGroup($careGroup);
            $entity->setCareLevel($careLevel);
        }

        return $entity;
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            /** @var ResidentAdmission $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $id);

            if ($entity === null) {
                throw new ResidentAdmissionNotFoundException();
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
                throw new ResidentAdmissionNotFoundException();
            }

            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            $residentAdmissions = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $ids);

            if (empty($residentAdmissions)) {
                throw new ResidentAdmissionNotFoundException();
            }

            /**
             * @var ResidentAdmission $residentAdmission
             */
            foreach ($residentAdmissions as $residentAdmission) {
                $this->em->remove($residentAdmission);
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
            throw new ResidentAdmissionNotFoundException();
        }

        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $ids);

        if (empty($entities)) {
            throw new ResidentAdmissionNotFoundException();
        }

        return $this->getRelatedData(ResidentAdmission::class, $entities);
    }
}
