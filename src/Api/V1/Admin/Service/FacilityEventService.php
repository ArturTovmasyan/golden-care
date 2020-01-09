<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\EndDateNotBeBlankException;
use App\Api\V1\Common\Service\Exception\EventDefinitionNotFoundException;
use App\Api\V1\Common\Service\Exception\NotAValidChoiceException;
use App\Api\V1\Common\Service\Exception\FacilityEventNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotBeBlankException;
use App\Api\V1\Common\Service\Exception\StartGreaterEndDateException;
use App\Api\V1\Common\Service\Exception\UserNotBeBlankException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\EventDefinition;
use App\Entity\Facility;
use App\Entity\FacilityEvent;
use App\Entity\Resident;
use App\Entity\User;
use App\Model\RepeatType;
use App\Repository\EventDefinitionRepository;
use App\Repository\FacilityEventRepository;
use App\Repository\FacilityRepository;
use App\Repository\ResidentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityEventService
 * @package App\Api\V1\Admin\Service
 */
class FacilityEventService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        if (empty($params) || empty($params[0]['facility_id'])) {
            throw new FacilityNotFoundException();
        }

        $facilityId = $params[0]['facility_id'];

        $queryBuilder
            ->where('fe.facility = :facilityId')
            ->setParameter('facilityId', $facilityId);

        /** @var FacilityEventRepository $repo */
        $repo = $this->em->getRepository(FacilityEvent::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityEvent::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['facility_id'])) {
            $facilityId = $params[0]['facility_id'];

            /** @var FacilityEventRepository $repo */
            $repo = $this->em->getRepository(FacilityEvent::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityEvent::class), $facilityId);
        }

        throw new FacilityNotFoundException();
    }

    /**
     * @param $id
     * @return FacilityEvent|null|object
     */
    public function getById($id)
    {
        /** @var FacilityEventRepository $repo */
        $repo = $this->em->getRepository(FacilityEvent::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityEvent::class), $id);
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

            $definitionId = $params['definition_id'] ?? 0;

            /** @var EventDefinitionRepository $definitionRepo */
            $definitionRepo = $this->em->getRepository(EventDefinition::class);

            /** @var EventDefinition $definition */
            $definition = $definitionRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(EventDefinition::class), $definitionId);

            if ($definition === null) {
                throw new EventDefinitionNotFoundException();
            }

            $users = [];
            if ($definition && $definition->isUsers()) {
                if (!empty($params['users'])) {
                    /** @var UserRepository $userRepo */
                    $userRepo = $this->em->getRepository(User::class);

                    $userIds = array_unique($params['users']);
                    $users = $userRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(User::class), $userIds);

                    if (empty($users)) {
                        throw new UserNotBeBlankException();
                    }
                } else {
                    throw new UserNotBeBlankException();
                }
            }

            $residents = [];
            if ($definition && $definition->isResidents()) {
                if (!empty($params['residents'])) {
                    /** @var ResidentRepository $residentRepo */
                    $residentRepo = $this->em->getRepository(Resident::class);

                    $residentIds = array_unique($params['residents']);
                    $residents = $residentRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentIds);

                    if (empty($residents)) {
                        throw new ResidentNotBeBlankException();
                    }
                } else {
                    throw new ResidentNotBeBlankException();
                }
            }

            $facilityEvent = new FacilityEvent();
            $facilityEvent->setFacility($facility);
            $facilityEvent->setDefinition($definition);
            $facilityEvent->setNotes($params['notes']);

            if (!empty($params['title'])) {
                $facilityEvent->setTitle($params['title']);
            } else{
                $facilityEvent->setTitle($definition->getTitle());
            }

            if (!empty($users)) {
                $facilityEvent->setUsers($users);
            }

            if (!empty($residents)) {
                $facilityEvent->setResidents($residents);
            }

            $start = null;
            if (!empty($params['start_date'])) {
                $start = new \DateTime($params['start_date']);

                if (!empty($params['start_time'])) {
                    $startTime = new \DateTime($params['start_time']);

                    $start->setTime($startTime->format('H'), $startTime->format('i'), $startTime->format('s'));
                }
            }

            $allDay = false;
            $end = null;
            if ($definition && $definition->isDuration()) {
                if (!empty($params['all_day'])) {
                    $allDay = (bool) $params['all_day'];
                }

                if ($allDay) {
                    $start->setTime(0, 0, 0);

                    $end = clone $start;
                    $end->setTime(23, 59, 59);
                }

                if (!$allDay && !empty($params['end_date'])) {
                    $end = new \DateTime($params['end_date']);

                    if (!empty($params['end_time'])) {
                        $endTime = new \DateTime($params['end_time']);

                        $end->setTime($endTime->format('H'), $endTime->format('i'), $endTime->format('s'));
                    }
                }

                if ($end === null) {
                    throw new EndDateNotBeBlankException();
                }

                if ($start !== null && $end !== null && $start > $end) {
                    throw new StartGreaterEndDateException();
                }
            }

            $facilityEvent->setStart($start);
            $facilityEvent->setAllDay($allDay);
            $facilityEvent->setEnd($end);

            $repeat = null;
            $repeatEnd = null;
            if ($definition && $definition->isRepeats()) {
                if (!empty($params['repeat'])) {
                    $repeat = (int) $params['repeat'];
                }

                if (!\in_array($repeat, RepeatType::getTypeValues(), false)) {
                    throw new NotAValidChoiceException();
                }

                if (!empty($params['repeat_end'])) {
                    $repeatEnd = new \DateTime($params['repeat_end']);

                    if ($end !== null && $end > $repeatEnd) {
                        throw new NotAValidChoiceException();
                    }

                    if ($end === null && $start !== null && $start > $repeatEnd) {
                        throw new NotAValidChoiceException();
                    }
                }
            }

            $facilityEvent->setRepeat($repeat);
            $facilityEvent->setRepeatEnd($repeatEnd);

            $rsvp = false;
            if ($definition && $definition->isRsvp()) {
                $rsvp = (bool) $params['rsvp'];
            }

            $facilityEvent->setRsvp($rsvp);

            $noRepeatEnd = !empty($params['no_repeat_end']) ? (bool) $params['no_repeat_end'] : false;

            $facilityEvent->setNoRepeatEnd($noRepeatEnd);

            $this->validate($facilityEvent, null, ['api_admin_facility_event_add']);

            $this->em->persist($facilityEvent);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $facilityEvent->getId();
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

            /** @var FacilityEventRepository $repo */
            $repo = $this->em->getRepository(FacilityEvent::class);

            /** @var FacilityEvent $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityEvent::class), $id);

            if ($entity === null) {
                throw new FacilityEventNotFoundException();
            }

            $facilityId = $params['facility_id'] ?? 0;

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            /** @var Facility $facility */
            $facility = $facilityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);

            if ($facility === null) {
                throw new FacilityNotFoundException();
            }

            $definitionId = $params['definition_id'] ?? 0;

            /** @var EventDefinitionRepository $definitionRepo */
            $definitionRepo = $this->em->getRepository(EventDefinition::class);

            /** @var EventDefinition $definition */
            $definition = $definitionRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(EventDefinition::class), $definitionId);

            if ($definition === null) {
                throw new EventDefinitionNotFoundException();
            }

            $oldUsers = $entity->getUsers();
            foreach ($oldUsers as $oldUser) {
                $entity->removeUser($oldUser);
            }

            $users = [];
            if ($definition && $definition->isUsers()) {
                if (!empty($params['users'])) {
                    /** @var UserRepository $userRepo */
                    $userRepo = $this->em->getRepository(User::class);

                    $userIds = array_unique($params['users']);
                    $users = $userRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(User::class), $userIds);

                    if (empty($users)) {
                        throw new UserNotBeBlankException();
                    }
                } else {
                    throw new UserNotBeBlankException();
                }
            }

            $oldResidents = $entity->getResidents();
            foreach ($oldResidents as $oldResident) {
                $entity->removeResident($oldResident);
            }

            $residents = [];
            if ($definition && $definition->isResidents()) {
                if (!empty($params['residents'])) {
                    /** @var ResidentRepository $residentRepo */
                    $residentRepo = $this->em->getRepository(Resident::class);

                    $residentIds = array_unique($params['residents']);
                    $residents = $residentRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentIds);

                    if (empty($residents)) {
                        throw new ResidentNotBeBlankException();
                    }
                } else {
                    throw new ResidentNotBeBlankException();
                }
            }

            $entity->setFacility($facility);
            $entity->setDefinition($definition);
            $entity->setNotes($params['notes']);

            if (!empty($params['title'])) {
                $entity->setTitle($params['title']);
            } else{
                $entity->setTitle($definition->getTitle());
            }

            if (!empty($users)) {
                $entity->setUsers($users);
            }

            if (!empty($residents)) {
                $entity->setResidents($residents);
            }

            $start = null;
            if (!empty($params['start_date'])) {
                $start = new \DateTime($params['start_date']);

                if (!empty($params['start_time'])) {
                    $startTime = new \DateTime($params['start_time']);

                    $start->setTime($startTime->format('H'), $startTime->format('i'), $startTime->format('s'));
                }
            }

            $allDay = false;
            $end = null;
            if ($definition && $definition->isDuration()) {
                if (!empty($params['all_day'])) {
                    $allDay = (bool) $params['all_day'];
                }

                if ($allDay) {
                    $start->setTime(0, 0, 0);

                    $end = clone $start;
                    $end->setTime(23, 59, 59);
                }

                if (!$allDay && !empty($params['end_date'])) {
                    $end = new \DateTime($params['end_date']);

                    if (!empty($params['end_time'])) {
                        $endTime = new \DateTime($params['end_time']);

                        $end->setTime($endTime->format('H'), $endTime->format('i'), $endTime->format('s'));
                    }
                }

                if ($end === null) {
                    throw new EndDateNotBeBlankException();
                }

                if ($start !== null && $end !== null && $start > $end) {
                    throw new StartGreaterEndDateException();
                }
            }

            $entity->setStart($start);
            $entity->setAllDay($allDay);
            $entity->setEnd($end);

            $repeat = null;
            $repeatEnd = null;
            if ($definition && $definition->isRepeats()) {
                if (!empty($params['repeat'])) {
                    $repeat = (int) $params['repeat'];
                }

                if (!\in_array($repeat, RepeatType::getTypeValues(), false)) {
                    throw new NotAValidChoiceException();
                }

                if (!empty($params['repeat_end'])) {
                    $repeatEnd = new \DateTime($params['repeat_end']);

                    if ($end !== null && $end > $repeatEnd) {
                        throw new NotAValidChoiceException();
                    }

                    if ($end === null && $start !== null && $start > $repeatEnd) {
                        throw new NotAValidChoiceException();
                    }
                }
            }

            $entity->setRepeat($repeat);
            $entity->setRepeatEnd($repeatEnd);

            $rsvp = false;
            if ($definition && $definition->isRsvp()) {
                $rsvp = (bool) $params['rsvp'];
            }

            $entity->setRsvp($rsvp);

            $noRepeatEnd = !empty($params['no_repeat_end']) ? (bool) $params['no_repeat_end'] : false;

            $entity->setNoRepeatEnd($noRepeatEnd);

            $this->validate($entity, null, ['api_admin_facility_event_edit']);

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
     * @param User $user
     * @return bool
     */
    public function getIsRsvp($id, User $user): bool
    {
        $isRsvp = false;

        /** @var FacilityEventRepository $repo */
        $repo = $this->em->getRepository(FacilityEvent::class);

        /** @var FacilityEvent $entity */
        $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityEvent::class), $id);
        
        if ($entity !== null && $user !== null && $this->grantService->hasCurrentUserGrant('activity-rsvp_facility_event')) {
            $isRsvp = $entity->isRsvp();
        }

        return $isRsvp;
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var FacilityEventRepository $repo */
            $repo = $this->em->getRepository(FacilityEvent::class);

            /** @var FacilityEvent $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityEvent::class), $id);

            if ($entity === null) {
                throw new FacilityEventNotFoundException();
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
                throw new FacilityEventNotFoundException();
            }

            /** @var FacilityEventRepository $repo */
            $repo = $this->em->getRepository(FacilityEvent::class);

            $facilityEvents = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityEvent::class), $ids);

            if (empty($facilityEvents)) {
                throw new FacilityEventNotFoundException();
            }

            /**
             * @var FacilityEvent $facilityEvent
             */
            foreach ($facilityEvents as $facilityEvent) {
                $this->em->remove($facilityEvent);
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
            throw new FacilityEventNotFoundException();
        }

        /** @var FacilityEventRepository $repo */
        $repo = $this->em->getRepository(FacilityEvent::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityEvent::class), $ids);

        if (empty($entities)) {
            throw new FacilityEventNotFoundException();
        }

        return $this->getRelatedData(FacilityEvent::class, $entities);
    }
}
