<?php
namespace App\Api\V1\Common\Service;

use App\Annotation\ValidationSerializedName;
use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Region;
use App\Entity\ResidentAdmission;
use App\Entity\Role;
use App\Entity\Space;
use App\Model\Grant;
use App\Model\GroupType;
use App\Repository\ApartmentBedRepository;
use App\Repository\ApartmentRepository;
use App\Repository\ApartmentRoomRepository;
use App\Repository\FacilityBedRepository;
use App\Repository\FacilityRepository;
use App\Repository\FacilityRoomRepository;
use App\Repository\RegionRepository;
use App\Repository\ResidentAdmissionRepository;
use App\Util\Mailer;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseService
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $encoder;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var Security
     */
    protected $security;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var GrantService
     */
    protected $grantService;

    /**
     * BaseService constructor.
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @param Mailer $mailer
     * @param ValidatorInterface $validator
     * @param Security $security
     * @param Reader $reader
     * @param GrantService $grantService
     */
    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder,
        Mailer $mailer,
        ValidatorInterface $validator,
        Security $security,
        Reader $reader,
        GrantService $grantService
    ) {
        $this->em           = $em;
        $this->encoder      = $encoder;
        $this->mailer       = $mailer;
        $this->validator    = $validator;
        $this->security     = $security;
        $this->reader       = $reader;
        $this->grantService = $grantService;
    }

    /**
     * @param $entity
     * @param null $constraints
     * @param null $groups
     * @return bool
     * @throws \ReflectionException
     */
    protected function validate($entity, $constraints = null, $groups = null)
    {
        $validationErrors = $this->validator->validate($entity, $constraints, $groups);
        $errors           = [];

        if ($validationErrors->count() > 0) {
            foreach ($validationErrors as $error) {
                $propertyPath = ValidationSerializedName::convert(
                    $this->reader,
                    $this->em->getClassMetadata(\get_class($entity))->getName(),
                    $groups[0],
                    $error->getPropertyPath()
                );

                $errors[$propertyPath] = $error->getMessage();
            }

            throw new ValidationException($errors);
        }

        return true;
    }

    /**
     * @param int $length
     * @return bool|string
     */
    protected function generatePassword($length = 8)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";

        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * @param $spaceId
     * @return Space|null
     */
    protected function getSpace($spaceId) : ?Space
    {
        /** @var Space $space */
        $space = $this->grantService->getCurrentSpace();

        // TODO: revisit null case
        if($spaceId !== null && $this->grantService->hasCurrentUserGrant('persistence-security-space')) {
            $space = $this->em->getRepository(Space::class)->find($spaceId);
        }

        return $space;
    }

    /**
     * @param $className
     * @param $entities
     * @return array
     */
    protected function getRelatedData($className, $entities) : array
    {
        $relatedData = [];
        if (!empty($entities)) {
            $classMetadata = $this->em->getClassMetadata($className);
            $associationMappings = $classMetadata->getAssociationMappings();

            foreach ($entities as $entity) {

                $relatedData[$entity->getId()]['sum'] = 0;

                if (!empty($associationMappings)) {
                    foreach ($associationMappings as $associationMapping) {
                        $mappedBy = null;
                        $id = null;
                        $ids = null;
                        if ($associationMapping['type'] === ClassMetadataInfo::MANY_TO_MANY) {
                            $getter = $entity->{'get' . ucfirst($associationMapping['fieldName'])}();

                            if ($associationMapping['targetEntity'] === Role::class) {
                                $getter = $entity->{'getRoleObjects'}();
                            }

                            if (\count($getter)) {
                                $ids = array_map(function($item){return $item->getId();} , $getter->toArray());
                            }
                        } else {
                            $mappedBy = $associationMapping['mappedBy'];
                            $id = $entity->getId();
                        }

                        if ($associationMapping['type'] === ClassMetadataInfo::MANY_TO_MANY || ($associationMapping['isOwningSide'] === false && ($associationMapping['type'] === ClassMetadataInfo::ONE_TO_MANY || $associationMapping['type'] === ClassMetadataInfo::ONE_TO_ONE))) {
                            $targetEntityName = explode('\\',$associationMapping['targetEntity']);
                            $targetEntityName = lcfirst(end($targetEntityName)) . 's';

                            $targetEntityRepo = $this->em->getRepository($associationMapping['targetEntity']);

                            $targetEntities = [];
                            if($targetEntityRepo instanceof RelatedInfoInterface) {
                                $targetEntities = $targetEntityRepo->getRelatedData($this->grantService->getCurrentSpace(), null, $mappedBy, $id, $ids);
                            }

                            $count = 0;
                            if (!empty($targetEntities)) {
                                $count = \count($targetEntities);
                            }

                            $hasAccessToView = $this->grantService->hasCurrentUserEntityGrant($associationMapping['targetEntity'], Grant::$LEVEL_VIEW);

                            if ($hasAccessToView) {
                                $targetEntities = [];
                                if($targetEntityRepo instanceof RelatedInfoInterface) {
                                    $targetEntities = $targetEntityRepo->getRelatedData($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants($associationMapping['targetEntity']), $mappedBy, $id, $ids);
                                }
                            } else {
                                $targetEntities = [];
                            }

                            $relatedData[$entity->getId()][] = [
                                'targetEntity' => $associationMapping['targetEntity'],
                                $targetEntityName => $targetEntities,
                                'count' => $count
                            ];

                            $relatedData[$entity->getId()]['sum'] += $count;
                        }
                    }
                }
            }
        }

        return $relatedData;
    }

    /**
     * @return array|null
     */
    public function getNotGrantResidentIds(): ?array
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var ResidentAdmissionRepository $admissionRepo */
        $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

        $facilityNotGrantResidents = [];
        $facilityEntityGrants = $this->grantService->getCurrentUserEntityGrants(Facility::class);
        if ($facilityEntityGrants !== null) {
            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            $facilities = $facilityRepo->getNotEntityGrants($currentSpace, $facilityEntityGrants);
            if (!empty($facilities)) {
                $tmpResidents = [];
                /** @var Facility $facility */
                foreach ($facilities as $facility) {
                    $tmpResidents[] = array_column($admissionRepo->getActiveResidentsByStrategy($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $facility->getId()), 'id');
                    $tmpResidents[] = array_column($admissionRepo->getInactiveResidentsByStrategy($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $facility->getId()), 'id');
                }

                $facilityNotGrantResidents = array_reduce($tmpResidents, 'array_merge', []);
            }
        }

        $apartmentNotGrantResidents = [];
        $apartmentEntityGrants = $this->grantService->getCurrentUserEntityGrants(Apartment::class);
        if ($apartmentEntityGrants !== null) {
            /** @var ApartmentRepository $apartmentRepo */
            $apartmentRepo = $this->em->getRepository(Apartment::class);

            $apartments = $apartmentRepo->getNotEntityGrants($currentSpace, $apartmentEntityGrants);
            if (!empty($apartments)) {
                $tmpResidents = [];
                /** @var Apartment $apartment */
                foreach ($apartments as $apartment) {
                    $tmpResidents[] = array_column($admissionRepo->getActiveResidentsByStrategy($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_APARTMENT, $apartment->getId()), 'id');
                    $tmpResidents[] = array_column($admissionRepo->getInactiveResidentsByStrategy($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_APARTMENT, $apartment->getId()), 'id');
                }

                $apartmentNotGrantResidents = array_reduce($tmpResidents, 'array_merge', []);
            }
        }

        $regionNotGrantResidents = [];
        $regionEntityGrants = $this->grantService->getCurrentUserEntityGrants(Region::class);
        if ($regionEntityGrants !== null) {
            /** @var RegionRepository $regionRepo */
            $regionRepo = $this->em->getRepository(Region::class);

            $regions = $regionRepo->getNotEntityGrants($currentSpace, $regionEntityGrants);
            if (!empty($regions)) {
                $tmpResidents = [];
                /** @var Region $region */
                foreach ($regions as $region) {
                    $tmpResidents[] = array_column($admissionRepo->getActiveResidentsByStrategy($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_REGION, $region->getId()), 'id');
                    $tmpResidents[] = array_column($admissionRepo->getInactiveResidentsByStrategy($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_REGION, $region->getId()), 'id');
                }

                $regionNotGrantResidents = array_reduce($tmpResidents, 'array_merge', []);
            }
        }

        $notGrantResidents = array_merge($facilityNotGrantResidents, $apartmentNotGrantResidents, $regionNotGrantResidents);
        $notGrantResidents = array_unique($notGrantResidents);

        return !empty($notGrantResidents) ? $notGrantResidents : null;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @return array
     */
    public function getRoomVacancyList($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var ResidentAdmissionRepository $admissionRepo */
        $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

        $all = $groupAll;
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_APARTMENT], false)) {
            throw new InvalidParameterException('group');
        }

        $rooms = [];
        $data = [];

        if ($type === GroupType::TYPE_FACILITY) {
            /** @var FacilityRoomRepository $facilityRoomRepo */
            $facilityRoomRepo = $this->em->getRepository(FacilityRoom::class);

            if ($typeId) {
                $rooms = $facilityRoomRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $typeId);
            }

            if ($all) {
                $rooms = $facilityRoomRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class));
            }

            $occupancyBedIds = [];
            if (!empty($rooms)) {

                $roomIds = array_map(function (FacilityRoom $item) {
                    return $item->getId();
                }, $rooms);

                /** @var FacilityBedRepository $facilityBedRepo */
                $facilityBedRepo = $this->em->getRepository(FacilityBed::class);

                $facilityBeds = $facilityBedRepo->getBedIdAndTypeIdByRooms($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityBed::class), $roomIds);

                if (\count($facilityBeds)) {
                    $bedIds = array_map(function($item){return $item['id'];} , $facilityBeds);

                    $admissions = $admissionRepo->getBedIdAndTypeId($currentSpace, $this->grantService->getCurrentUserEntityGrants( ResidentAdmission::class), GroupType::TYPE_FACILITY, $bedIds);

                    if (!empty($admissions)) {
                        $occupancyBedIds = array_map(function($item){return $item['bedId'];} , $admissions);
                    }

                    foreach ($facilityBeds as $bed) {
                        if (!\in_array($bed['id'], $occupancyBedIds, false)) {
                            $data[] = $bed;
                        }
                    }
                }
            }
        } elseif ($type === GroupType::TYPE_APARTMENT) {
            /** @var ApartmentRoomRepository $apartmentRoomRepo */
            $apartmentRoomRepo = $this->em->getRepository(ApartmentRoom::class);

            if ($typeId) {
                $rooms = $apartmentRoomRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $typeId);
            }

            if ($all) {
                $rooms = $apartmentRoomRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class));
            }

            $occupancyBedIds = [];
            if (!empty($rooms)) {

                $roomIds = array_map(function (ApartmentRoom $item) {
                    return $item->getId();
                }, $rooms);

                /** @var ApartmentBedRepository $apartmentBedRepo */
                $apartmentBedRepo = $this->em->getRepository(ApartmentBed::class);

                $apartmentBeds = $apartmentBedRepo->getBedIdAndTypeIdByRooms($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentBed::class), $roomIds);

                if (\count($apartmentBeds)) {
                    $bedIds = array_map(function($item){return $item['id'];} , $apartmentBeds);

                    $admissions = $admissionRepo->getBedIdAndTypeId($currentSpace, $this->grantService->getCurrentUserEntityGrants( ResidentAdmission::class), GroupType::TYPE_APARTMENT, $bedIds);

                    if (!empty($admissions)) {
                        $occupancyBedIds = array_map(function($item){return $item['bedId'];} , $admissions);
                    }

                    foreach ($apartmentBeds as $bed) {
                        if (!\in_array($bed['id'], $occupancyBedIds, false)) {
                            $data[] = $bed;
                        }
                    }
                }
            }
        }

        return $data;
    }
}