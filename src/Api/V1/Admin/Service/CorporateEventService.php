<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\EndDateNotBeBlankException;
use App\Api\V1\Common\Service\Exception\EventDefinitionNotFoundException;
use App\Api\V1\Common\Service\Exception\NotAValidChoiceException;
use App\Api\V1\Common\Service\Exception\CorporateEventNotFoundException;
use App\Api\V1\Common\Service\Exception\StartGreaterEndDateException;
use App\Api\V1\Common\Service\Exception\UserNotBeBlankException;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CorporateEventUser;
use App\Entity\EventDefinition;
use App\Entity\Facility;
use App\Entity\CorporateEvent;
use App\Entity\Role;
use App\Entity\User;
use App\Model\RepeatType;
use App\Repository\CorporateEventUserRepository;
use App\Repository\EventDefinitionRepository;
use App\Repository\CorporateEventRepository;
use App\Repository\FacilityRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CorporateEventService
 * @package App\Api\V1\Admin\Service
 */
class CorporateEventService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var CorporateEventRepository $repo */
        $repo = $this->em->getRepository(CorporateEvent::class);

        $facilityEntityGrants = !empty($this->grantService->getCurrentUserEntityGrants(Facility::class)) ? $this->grantService->getCurrentUserEntityGrants(Facility::class) : null;

        $userRoleIds = null;
        if (!empty($params) || !empty($params[0]['user_role_ids'])) {
            $userRoleIds = $params[0]['user_role_ids'];
        }

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CorporateEvent::class), $facilityEntityGrants, $queryBuilder, $userRoleIds);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var CorporateEventRepository $repo */
        $repo = $this->em->getRepository(CorporateEvent::class);

        $facilityEntityGrants = !empty($this->grantService->getCurrentUserEntityGrants(Facility::class)) ? $this->grantService->getCurrentUserEntityGrants(Facility::class) : null;

        $userRoleIds = null;
        if (!empty($params) || !empty($params[0]['user_role_ids'])) {
            $userRoleIds = $params[0]['user_role_ids'];
        }

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CorporateEvent::class), $facilityEntityGrants, $userRoleIds);
    }

    /**
     * @param $id
     * @return CorporateEvent|null|object
     */
    public function getById($id)
    {
        /** @var CorporateEventRepository $repo */
        $repo = $this->em->getRepository(CorporateEvent::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CorporateEvent::class), $id);
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

            $definitionId = $params['definition_id'] ?? 0;

            /** @var EventDefinitionRepository $definitionRepo */
            $definitionRepo = $this->em->getRepository(EventDefinition::class);

            /** @var EventDefinition $definition */
            $definition = $definitionRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(EventDefinition::class), $definitionId);

            if ($definition === null) {
                throw new EventDefinitionNotFoundException();
            }

            $corporateEvent = new CorporateEvent();
            $corporateEvent->setDefinition($definition);
            $corporateEvent->setNotes($params['notes']);

            if (!empty($params['title'])) {
                $corporateEvent->setTitle($params['title']);
            } else {
                $corporateEvent->setTitle($definition->getTitle());
            }

            $start = null;
            if (!empty($params['start_date'])) {
                if (!empty($params['start_time'])) {
                    $start = new \DateTime($params['start_date'] . ' ' . $params['start_time']);
                } else {
                    $start = new \DateTime($params['start_date']);
                }
            }

            $allDay = false;
            $end = null;
            if ($definition->isDuration()) {
                if (!empty($params['all_day'])) {
                    $allDay = (bool)$params['all_day'];
                }

                if ($allDay) {
                    $start->setTime(0, 0, 0);

                    $end = clone $start;
                    $end->setTime(23, 59, 59);
                }

                if (!$allDay && !empty($params['end_date'])) {
                    if (!empty($params['end_time'])) {
                        $end = new \DateTime($params['end_date'] . ' ' . $params['end_time']);
                    } else {
                        $end = new \DateTime($params['end_date']);
                    }
                }

                if ($end === null) {
                    throw new EndDateNotBeBlankException();
                }

                if ($start !== null && $end !== null && $start > $end) {
                    throw new StartGreaterEndDateException();
                }
            }

            $corporateEvent->setStart($start);
            $corporateEvent->setAllDay($allDay);
            $corporateEvent->setEnd($end);

            $repeat = null;
            $repeatEnd = null;
            if ($definition->isRepeats()) {
                if (!empty($params['repeat'])) {
                    $repeat = (int)$params['repeat'];
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

            $corporateEvent->setRepeat($repeat);
            $corporateEvent->setRepeatEnd($repeatEnd);

            $rsvp = false;
            if ($definition->isRsvp()) {
                $rsvp = (bool)$params['rsvp'];
            }

            $corporateEvent->setRsvp($rsvp);

            $noRepeatEnd = !empty($params['no_repeat_end']) ? (bool)$params['no_repeat_end'] : false;

            $corporateEvent->setNoRepeatEnd($noRepeatEnd);

            if (!empty($params['facilities'])) {
                /** @var FacilityRepository $facilityRepo */
                $facilityRepo = $this->em->getRepository(Facility::class);

                $facilityIds = array_unique($params['facilities']);
                $facilities = $facilityRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityIds);

                if (!empty($facilities)) {
                    $corporateEvent->setFacilities($facilities);
                } else {
                    $corporateEvent->setFacilities(null);
                }
            } else {
                $corporateEvent->setFacilities(null);
            }

            if (!empty($params['roles'])) {
                /** @var RoleRepository $roleRepo */
                $roleRepo = $this->em->getRepository(Role::class);

                $roleIds = array_unique($params['roles']);
                $roles = $roleRepo->findByIds($roleIds);

                if (!empty($roles)) {
                    $corporateEvent->setRoles($roles);
                } else {
                    $corporateEvent->setRoles(null);
                }
            } else {
                $corporateEvent->setRoles(null);
            }

            if ($definition->isUsers()) {
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

                $corporateEvent->setCorporateEventUsers($this->saveCorporateEventUsers($definition->isDone(), $corporateEvent, $users));
            }

            $this->validate($corporateEvent, null, ['api_admin_corporate_event_add']);

            $this->em->persist($corporateEvent);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $corporateEvent->getId();
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

            /** @var CorporateEventRepository $repo */
            $repo = $this->em->getRepository(CorporateEvent::class);

            /** @var CorporateEvent $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CorporateEvent::class), $id);

            if ($entity === null) {
                throw new CorporateEventNotFoundException();
            }

            $definitionId = $params['definition_id'] ?? 0;

            /** @var EventDefinitionRepository $definitionRepo */
            $definitionRepo = $this->em->getRepository(EventDefinition::class);

            /** @var EventDefinition $definition */
            $definition = $definitionRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(EventDefinition::class), $definitionId);

            if ($definition === null) {
                throw new EventDefinitionNotFoundException();
            }

            $entity->setDefinition($definition);
            $entity->setNotes($params['notes']);

            if (!empty($params['title'])) {
                $entity->setTitle($params['title']);
            } else {
                $entity->setTitle($definition->getTitle());
            }

            $start = null;
            if (!empty($params['start_date'])) {
                if (!empty($params['start_time'])) {
                    $start = new \DateTime($params['start_date'] . ' ' . $params['start_time']);
                } else {
                    $start = new \DateTime($params['start_date']);
                }
            }

            $allDay = false;
            $end = null;
            if ($definition && $definition->isDuration()) {
                if (!empty($params['all_day'])) {
                    $allDay = (bool)$params['all_day'];
                }

                if ($allDay) {
                    $start->setTime(0, 0, 0);

                    $end = clone $start;
                    $end->setTime(23, 59, 59);
                }

                if (!$allDay && !empty($params['end_date'])) {
                    if (!empty($params['end_time'])) {
                        $end = new \DateTime($params['end_date'] . ' ' . $params['end_time']);
                    } else {
                        $end = new \DateTime($params['end_date']);
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
                    $repeat = (int)$params['repeat'];
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
                $rsvp = (bool)$params['rsvp'];
            }

            $entity->setRsvp($rsvp);

            $noRepeatEnd = !empty($params['no_repeat_end']) ? (bool)$params['no_repeat_end'] : false;

            $entity->setNoRepeatEnd($noRepeatEnd);

            $facilities = $entity->getFacilities();
            foreach ($facilities as $facility) {
                $entity->removeFacility($facility);
            }

            if (!empty($params['facilities'])) {
                /** @var FacilityRepository $facilityRepo */
                $facilityRepo = $this->em->getRepository(Facility::class);

                $facilityIds = array_unique($params['facilities']);
                $facilities = $facilityRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityIds);

                if (!empty($facilities)) {
                    $entity->setFacilities($facilities);
                } else {
                    $entity->setFacilities(null);
                }
            } else {
                $entity->setFacilities(null);
            }

            $roles = $entity->getRoles();
            foreach ($roles as $role) {
                $entity->removeRole($role);
            }

            if (!empty($params['roles'])) {
                /** @var RoleRepository $roleRepo */
                $roleRepo = $this->em->getRepository(Role::class);

                $roleIds = array_unique($params['roles']);
                $roles = $roleRepo->findByIds($roleIds);

                if (!empty($roles)) {
                    $entity->setRoles($roles);
                } else {
                    $entity->setRoles(null);
                }
            } else {
                $entity->setRoles(null);
            }

            if ($definition->isUsers()) {
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

                $entity->setCorporateEventUsers($this->saveCorporateEventUsers($definition->isDone(), $entity, $users));
            }

            $this->validate($entity, null, ['api_admin_corporate_event_edit']);

            $this->em->persist($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $isDone
     * @param CorporateEvent $event
     * @param $users
     * @return array
     */
    private function saveCorporateEventUsers($isDone, CorporateEvent $event, $users): ?array
    {
        $userDones = [];
        if ($event->getId() !== null) {
            /** @var CorporateEventUserRepository $corporateEventUserRepo */
            $corporateEventUserRepo = $this->em->getRepository(CorporateEventUser::class);

            $oldCorporateEventUsers = $corporateEventUserRepo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CorporateEventUser::class), $event);

            /** @var CorporateEventUser $oldCorporateEventUser */
            foreach ($oldCorporateEventUsers as $oldCorporateEventUser) {
                $user = $oldCorporateEventUser->getUser();

                if ($user !== null) {
                    $userDones[$user->getId()] = $oldCorporateEventUser->isDone();
                }

                $this->em->remove($oldCorporateEventUser);
            }
        }

        $corporateEventUsers = [];
        $done = true;
        /** @var User $user */
        foreach ($users as $user) {
            $corporateEventUser = new CorporateEventUser();
            $corporateEventUser->setEvent($event);
            $corporateEventUser->setUser($user);

            if ($isDone && array_key_exists($user->getId(), $userDones)) {
                $corporateEventUser->setDone($userDones[$user->getId()]);
            } else {
                $corporateEventUser->setDone(false);
            }

            $this->em->persist($corporateEventUser);

            if (!$corporateEventUser->isDone()) {
                $done = false;
            }

            $corporateEventUsers[] = $corporateEventUser;
        }

        $event->setDone($done);

        return $corporateEventUsers;
    }

    /**
     * @param $id
     * @param User $user
     * @param array $params
     * @throws \Exception
     */
    public function changeDoneByCurrentUser($id, User $user, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            if ($user === null) {
                throw new UserNotFoundException();
            }

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var CorporateEventRepository $repo */
            $repo = $this->em->getRepository(CorporateEvent::class);

            /** @var CorporateEvent $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CorporateEvent::class), $id);

            if ($entity === null) {
                throw new CorporateEventNotFoundException();
            }

            $definition = $entity->getDefinition();

            if ($definition === null) {
                throw new EventDefinitionNotFoundException();
            }

            $done = true;
            if ($definition->isUsers() && !empty($entity->getCorporateEventUsers())) {
                /** @var CorporateEventUser $corporateEventUser */
                foreach ($entity->getCorporateEventUsers() as $corporateEventUser) {
                    if ($corporateEventUser->getUser() !== null && $corporateEventUser->getUser()->getId() === $user->getId()) {
                        $corporateEventUser->setDone((bool)$params['done']);

                        $this->em->persist($corporateEventUser);
                    }

                    if (!$corporateEventUser->isDone()) {
                        $done = false;
                    }
                }
            }

            $entity->setDone($done);

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
    public function getIsDone($id, User $user): bool
    {
        $isDone = true;

        /** @var CorporateEventRepository $repo */
        $repo = $this->em->getRepository(CorporateEvent::class);

        /** @var CorporateEvent $entity */
        $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CorporateEvent::class), $id);

        if ($entity !== null && $user !== null && $entity->getDefinition() !== null && !empty($entity->getCorporateEventUsers()) && $entity->getDefinition()->isUsers()) {
            /** @var CorporateEventUser $corporateEventUser */
            foreach ($entity->getCorporateEventUsers() as $corporateEventUser) {
                if ($corporateEventUser->getUser() !== null && $corporateEventUser->getUser()->getId() === $user->getId()) {
                    $isDone = $corporateEventUser->isDone();
                    break;
                }
            }
        }

        return $isDone;
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var CorporateEventRepository $repo */
            $repo = $this->em->getRepository(CorporateEvent::class);

            /** @var CorporateEvent $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CorporateEvent::class), $id);

            if ($entity === null) {
                throw new CorporateEventNotFoundException();
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
                throw new CorporateEventNotFoundException();
            }

            /** @var CorporateEventRepository $repo */
            $repo = $this->em->getRepository(CorporateEvent::class);

            $corporateEvents = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CorporateEvent::class), $ids);

            if (empty($corporateEvents)) {
                throw new CorporateEventNotFoundException();
            }

            /**
             * @var CorporateEvent $corporateEvent
             */
            foreach ($corporateEvents as $corporateEvent) {
                $this->em->remove($corporateEvent);
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
            throw new CorporateEventNotFoundException();
        }

        /** @var CorporateEventRepository $repo */
        $repo = $this->em->getRepository(CorporateEvent::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CorporateEvent::class), $ids);

        if (empty($entities)) {
            throw new CorporateEventNotFoundException();
        }

        return $this->getRelatedData(CorporateEvent::class, $entities);
    }
}
