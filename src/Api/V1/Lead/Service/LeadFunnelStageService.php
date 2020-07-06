<?php

namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\ActivityStatusNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\FunnelStageNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\LeadFunnelStageNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\LeadNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\StageChangeReasonNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\Activity;
use App\Entity\Lead\ActivityStatus;
use App\Entity\Lead\ActivityType;
use App\Entity\Lead\FunnelStage;
use App\Entity\Lead\Lead;
use App\Entity\Lead\LeadFunnelStage;
use App\Entity\Lead\StageChangeReason;
use App\Model\Lead\ActivityOwnerType;
use App\Model\Lead\State;
use App\Repository\Lead\ActivityStatusRepository;
use App\Repository\Lead\ActivityTypeRepository;
use App\Repository\Lead\FunnelStageRepository;
use App\Repository\Lead\LeadFunnelStageRepository;
use App\Repository\Lead\LeadRepository;
use App\Repository\Lead\StageChangeReasonRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class LeadFunnelStageService
 * @package App\Api\V1\Admin\Service
 */
class LeadFunnelStageService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        if (empty($params) || empty($params[0]['lead_id'])) {
            throw new LeadNotFoundException();
        }

        $leadId = $params[0]['lead_id'];

        $queryBuilder
            ->where('lfs.lead = :leadId')
            ->setParameter('leadId', $leadId);

        /** @var LeadFunnelStageRepository $repo */
        $repo = $this->em->getRepository(LeadFunnelStage::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LeadFunnelStage::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var LeadFunnelStageRepository $repo */
        $repo = $this->em->getRepository(LeadFunnelStage::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LeadFunnelStage::class));
    }

    /**
     * @param $id
     * @return LeadFunnelStage|null|object
     */
    public function getById($id)
    {
        /** @var LeadFunnelStageRepository $repo */
        $repo = $this->em->getRepository(LeadFunnelStage::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LeadFunnelStage::class), $id);
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

            $leadId = $params['lead_id'] ?? 0;

            /** @var LeadRepository $leadRepo */
            $leadRepo = $this->em->getRepository(Lead::class);

            /** @var Lead $lead */
            $lead = $leadRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $leadId);

            if ($lead === null) {
                throw new LeadNotFoundException();
            }

            $stageId = $params['stage_id'] ?? 0;

            /** @var FunnelStageRepository $stageRepo */
            $stageRepo = $this->em->getRepository(FunnelStage::class);

            /** @var FunnelStage $stage */
            $stage = $stageRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FunnelStage::class), $stageId);

            if ($stage === null) {
                throw new FunnelStageNotFoundException();
            }

            $reasonId = $params['reason_id'] ?? 0;

            /** @var StageChangeReasonRepository $reasonRepo */
            $reasonRepo = $this->em->getRepository(StageChangeReason::class);

            /** @var StageChangeReason $reason */
            $reason = $reasonRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(StageChangeReason::class), $reasonId);

            if ($reason === null) {
                throw new StageChangeReasonNotFoundException();
            }

            $leadFunnelStage = new LeadFunnelStage();
            $leadFunnelStage->setLead($lead);
            $leadFunnelStage->setStage($stage);
            $leadFunnelStage->setReason($reason);

            $date = $params['date'];
            if (!empty($date)) {
                $date = new \DateTime($params['date']);

                $leadFunnelStage->setDate($date);
            } else {
                $leadFunnelStage->setDate(null);
            }

            $leadFunnelStage->setNotes($params['notes']);

            $this->validate($leadFunnelStage, null, ['api_lead_lead_funnel_stage_add']);

            $this->em->persist($leadFunnelStage);

            // Creating change funnel stage activity
            $this->createLeadChangeFunnelStageActivity($currentSpace, $lead, $leadFunnelStage);

            /** @var LeadFunnelStageRepository $repo */
            $repo = $this->em->getRepository(LeadFunnelStage::class);
            /** @var LeadFunnelStage $lastFunnelStage */
            $lastFunnelStage = $repo->getLastAction($currentSpace, $this->grantService->getCurrentUserEntityGrants(LeadFunnelStage::class), $lead->getId());

            if ($lastFunnelStage === null || ($lastFunnelStage !== null && $leadFunnelStage->getDate() > $lastFunnelStage->getDate())) {
                if ($stage->isOpen()) {
                    $state = State::TYPE_OPEN;
                } else {
                    $state = State::TYPE_CLOSED;

                    //set all lead activities statuses to Done when lead state are Closed
                    if ($lead->getActivities() !== null) {
                        /** @var ActivityStatusRepository $activityStatusRepo */
                        $activityStatusRepo = $this->em->getRepository(ActivityStatus::class);

                        $activityStatus = $activityStatusRepo->getDone($currentSpace);

                        if ($activityStatus === null) {
                            throw new ActivityStatusNotFoundException();
                        }

                        /** @var Activity $activity */
                        foreach ($lead->getActivities() as $activity) {
                            if ($activity->getStatus() === null || ($activity->getStatus() !== null && !$activity->getStatus()->isDone())) {
                                $activity->setStatus($activityStatus);
                            }

                            $this->em->persist($activity);
                        }
                    }
                }

                $lead->setState($state);
                $this->em->persist($lead);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $leadFunnelStage->getId();
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

            /** @var LeadFunnelStageRepository $repo */
            $repo = $this->em->getRepository(LeadFunnelStage::class);

            /** @var LeadFunnelStage $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(LeadFunnelStage::class), $id);

            if ($entity === null) {
                throw new LeadFunnelStageNotFoundException();
            }

            $leadId = $params['lead_id'] ?? 0;

            /** @var LeadRepository $leadRepo */
            $leadRepo = $this->em->getRepository(Lead::class);

            /** @var Lead $lead */
            $lead = $leadRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $leadId);

            if ($lead === null) {
                throw new LeadNotFoundException();
            }

            $stageId = $params['stage_id'] ?? 0;

            /** @var FunnelStageRepository $stageRepo */
            $stageRepo = $this->em->getRepository(FunnelStage::class);

            /** @var FunnelStage $stage */
            $stage = $stageRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FunnelStage::class), $stageId);

            if ($stage === null) {
                throw new FunnelStageNotFoundException();
            }

            $reasonId = $params['reason_id'] ?? 0;

            /** @var StageChangeReasonRepository $reasonRepo */
            $reasonRepo = $this->em->getRepository(StageChangeReason::class);

            /** @var StageChangeReason $reason */
            $reason = $reasonRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(StageChangeReason::class), $reasonId);

            if ($reason === null) {
                throw new StageChangeReasonNotFoundException();
            }

            $entity->setLead($lead);
            $entity->setStage($stage);
            $entity->setReason($reason);

            $date = $params['date'];
            if (!empty($date)) {
                $date = new \DateTime($params['date']);

                $entity->setDate($date);
            } else {
                $entity->setDate(null);
            }

            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_lead_lead_funnel_stage_edit']);

            $this->em->persist($entity);

            /** @var LeadFunnelStage $lastFunnelStage */
            $lastFunnelStage = $repo->getLastAction($currentSpace, $this->grantService->getCurrentUserEntityGrants(LeadFunnelStage::class), $lead->getId());

            if ($lastFunnelStage->getId() === $entity->getId() || $entity->getDate() > $lastFunnelStage->getDate()) {
                if ($stage->isOpen()) {
                    $state = State::TYPE_OPEN;
                } else {
                    $state = State::TYPE_CLOSED;

                    //set all lead activities statuses to Done when lead state are Closed
                    if ($lead->getActivities() !== null) {
                        /** @var ActivityStatusRepository $activityStatusRepo */
                        $activityStatusRepo = $this->em->getRepository(ActivityStatus::class);

                        $activityStatus = $activityStatusRepo->getDone($currentSpace);

                        if ($activityStatus === null) {
                            throw new ActivityStatusNotFoundException();
                        }

                        /** @var Activity $activity */
                        foreach ($lead->getActivities() as $activity) {
                            if ($activity->getStatus() === null || ($activity->getStatus() !== null && !$activity->getStatus()->isDone())) {
                                $activity->setStatus($activityStatus);
                            }

                            $this->em->persist($activity);
                        }
                    }
                }

                $lead->setState($state);
                $this->em->persist($lead);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $currentSpace
     * @param Lead $lead
     * @param LeadFunnelStage $leadFunnelStage
     */
    private function createLeadChangeFunnelStageActivity($currentSpace, Lead $lead, LeadFunnelStage $leadFunnelStage)
    {
        /** @var ActivityTypeRepository $typeRepo */
        $typeRepo = $this->em->getRepository(ActivityType::class);
        /** @var ActivityType $type */
        $type = $typeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ActivityType::class), 13);

        if ($type === null) {
            throw new ActivityTypeNotFoundException();
        }

        /** @var LeadFunnelStageRepository $repo */
        $repo = $this->em->getRepository(LeadFunnelStage::class);
        /** @var LeadFunnelStage $lastFunnelStage */
        $lastFunnelStage = $repo->getLastAction($currentSpace, $this->grantService->getCurrentUserEntityGrants(LeadFunnelStage::class), $lead->getId());

        if ($lastFunnelStage === null) {
            throw new LeadFunnelStageNotFoundException();
        }

        $lastFunnelStageTitle = $lastFunnelStage->getStage() ? $lastFunnelStage->getStage()->getTitle() : 'N/A';
        $currentFunnelStageTitle = $leadFunnelStage->getStage() ? $leadFunnelStage->getStage()->getTitle() : 'N/A';

        $notes = 'Changed funnel Stage from ' . $lastFunnelStageTitle . ' to ' . $currentFunnelStageTitle . '.';

        $activity = new Activity();
        $activity->setLead($lead);
        $activity->setType($type);
        $activity->setOwnerType(ActivityOwnerType::TYPE_LEAD);
        $activity->setDate($leadFunnelStage->getDate());
        $activity->setStatus($type->getDefaultStatus());
        $activity->setTitle($type->getTitle());
        $activity->setNotes($notes);
        $activity->setAssignTo(null);
        $activity->setDueDate(null);
        $activity->setReminderDate(null);
        $activity->setFacility(null);
        $activity->setReferral(null);
        $activity->setOrganization(null);

        $this->validate($activity, null, ['api_lead_lead_activity_add']);

        $this->em->persist($activity);
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

            /** @var LeadFunnelStageRepository $repo */
            $repo = $this->em->getRepository(LeadFunnelStage::class);

            /** @var LeadFunnelStage $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(LeadFunnelStage::class), $id);

            if ($entity === null) {
                throw new LeadFunnelStageNotFoundException();
            }

            $lead = $entity->getLead();
            if ($lead !== null) {
                $leadFunnelStages = $repo->getOrderedByDate($currentSpace, $this->grantService->getCurrentUserEntityGrants(LeadFunnelStage::class), $lead->getId());

                if (!empty($leadFunnelStages)) {
                    /** @var LeadFunnelStage $lastFunnelStage */
                    $lastFunnelStage = $leadFunnelStages[0];
                    if (array_key_exists(1, $leadFunnelStages) && $lastFunnelStage->getId() === $entity->getId()) {
                        /** @var LeadFunnelStage $leadFunnelStage */
                        $leadFunnelStage = $leadFunnelStages[1];

                        $stage = $leadFunnelStage->getStage();

                        if ($stage !== null) {
                            if ($stage->isOpen()) {
                                $state = State::TYPE_OPEN;
                            } else {
                                $state = State::TYPE_CLOSED;
                            }

                            $lead->setState($state);
                            $this->em->persist($lead);
                        }
                    }
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
    public function removeBulk(array $ids)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new LeadFunnelStageNotFoundException();
            }

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var LeadFunnelStageRepository $repo */
            $repo = $this->em->getRepository(LeadFunnelStage::class);

            $leadFunnelStages = $repo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(LeadFunnelStage::class), $ids);

            if (empty($leadFunnelStages)) {
                throw new LeadFunnelStageNotFoundException();
            }

            /**
             * @var LeadFunnelStage $leadFunnelStage
             */
            foreach ($leadFunnelStages as $leadFunnelStage) {
                $lead = $leadFunnelStage->getLead();
                if ($lead !== null) {
                    $funnelStages = $repo->getOrderedByDate($currentSpace, $this->grantService->getCurrentUserEntityGrants(LeadFunnelStage::class), $lead->getId());

                    if (!empty($funnelStages)) {
                        /** @var LeadFunnelStage $lastFunnelStage */
                        $lastFunnelStage = $funnelStages[0];
                        if (array_key_exists(1, $funnelStages) && $lastFunnelStage->getId() === $leadFunnelStage->getId()) {
                            /** @var LeadFunnelStage $funnelStage */
                            $funnelStage = $funnelStages[1];

                            $stage = $funnelStage->getStage();

                            if ($stage !== null) {
                                if ($stage->isOpen()) {
                                    $state = State::TYPE_OPEN;
                                } else {
                                    $state = State::TYPE_CLOSED;
                                }

                                $lead->setState($state);
                                $this->em->persist($lead);
                            }
                        }
                    }
                }

                $this->em->remove($leadFunnelStage);
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
            throw new LeadFunnelStageNotFoundException();
        }

        /** @var LeadFunnelStageRepository $repo */
        $repo = $this->em->getRepository(LeadFunnelStage::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LeadFunnelStage::class), $ids);

        if (empty($entities)) {
            throw new LeadFunnelStageNotFoundException();
        }

        return $this->getRelatedData(LeadFunnelStage::class, $entities);
    }
}
