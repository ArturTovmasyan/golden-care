<?php
namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\ActivityTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\TemperatureNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\LeadTemperatureNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\LeadNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\Activity;
use App\Entity\Lead\ActivityType;
use App\Entity\Lead\Temperature;
use App\Entity\Lead\Lead;
use App\Entity\Lead\LeadTemperature;
use App\Model\Lead\ActivityOwnerType;
use App\Repository\Lead\ActivityTypeRepository;
use App\Repository\Lead\TemperatureRepository;
use App\Repository\Lead\LeadTemperatureRepository;
use App\Repository\Lead\LeadRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class LeadTemperatureService
 * @package App\Api\V1\Admin\Service
 */
class LeadTemperatureService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        if (empty($params) || empty($params[0]['lead_id'])) {
            throw new LeadNotFoundException();
        }

        $leadId = $params[0]['lead_id'];

        $queryBuilder
            ->where('lt.lead = :leadId')
            ->setParameter('leadId', $leadId);

        /** @var LeadTemperatureRepository $repo */
        $repo = $this->em->getRepository(LeadTemperature::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LeadTemperature::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var LeadTemperatureRepository $repo */
        $repo = $this->em->getRepository(LeadTemperature::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LeadTemperature::class));
    }

    /**
     * @param $id
     * @return LeadTemperature|null|object
     */
    public function getById($id)
    {
        /** @var LeadTemperatureRepository $repo */
        $repo = $this->em->getRepository(LeadTemperature::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LeadTemperature::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params) : ?int
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

            $temperatureId = $params['temperature_id'] ?? 0;

            /** @var TemperatureRepository $temperatureRepo */
            $temperatureRepo = $this->em->getRepository(Temperature::class);

            /** @var Temperature $temperature */
            $temperature = $temperatureRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Temperature::class), $temperatureId);

            if ($temperature === null) {
                throw new TemperatureNotFoundException();
            }

            $leadTemperature = new LeadTemperature();
            $leadTemperature->setLead($lead);
            $leadTemperature->setTemperature($temperature);

            $date = $params['date'];
            if (!empty($date)) {
                $date = new \DateTime($params['date']);

                $leadTemperature->setDate($date);
            } else {
                $leadTemperature->setDate(null);
            }

            $leadTemperature->setNotes($params['notes']);

            $this->validate($leadTemperature, null, ['api_lead_lead_temperature_add']);

            $this->em->persist($leadTemperature);

            // Creating change temperature activity
            $this->createLeadChangeTemperatureActivity($currentSpace, $lead, $leadTemperature);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $leadTemperature->getId();
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
    public function edit($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var LeadTemperatureRepository $repo */
            $repo = $this->em->getRepository(LeadTemperature::class);

            /** @var LeadTemperature $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(LeadTemperature::class), $id);

            if ($entity === null) {
                throw new LeadTemperatureNotFoundException();
            }

            $leadId = $params['lead_id'] ?? 0;

            /** @var LeadRepository $leadRepo */
            $leadRepo = $this->em->getRepository(Lead::class);

            /** @var Lead $lead */
            $lead = $leadRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $leadId);

            if ($lead === null) {
                throw new LeadNotFoundException();
            }

            $temperatureId = $params['temperature_id'] ?? 0;

            /** @var TemperatureRepository $temperatureRepo */
            $temperatureRepo = $this->em->getRepository(Temperature::class);

            /** @var Temperature $temperature */
            $temperature = $temperatureRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Temperature::class), $temperatureId);

            if ($temperature === null) {
                throw new TemperatureNotFoundException();
            }

            $entity->setLead($lead);
            $entity->setTemperature($temperature);

            $date = $params['date'];
            if (!empty($date)) {
                $date = new \DateTime($params['date']);

                $entity->setDate($date);
            } else {
                $entity->setDate(null);
            }

            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_lead_lead_temperature_edit']);

            $this->em->persist($entity);
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
     * @param LeadTemperature $leadTemperature
     */
    private function createLeadChangeTemperatureActivity($currentSpace, Lead $lead, LeadTemperature $leadTemperature)
    {
        /** @var ActivityTypeRepository $typeRepo */
        $typeRepo = $this->em->getRepository(ActivityType::class);
        /** @var ActivityType $type */
        $type = $typeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ActivityType::class), 8);

        if ($type === null) {
            throw new ActivityTypeNotFoundException();
        }

        /** @var LeadTemperatureRepository $repo */
        $repo = $this->em->getRepository(LeadTemperature::class);
        /** @var LeadTemperature $lastTemperature */
        $lastTemperature = $repo->getLastAction($currentSpace, $this->grantService->getCurrentUserEntityGrants(LeadTemperature::class), $lead->getId());

        if ($lastTemperature === null) {
            throw new LeadTemperatureNotFoundException();
        }

        $lastTemperatureTitle = $lastTemperature->getTemperature() ? $lastTemperature->getTemperature()->getTitle() : 'N/A';
        $currentTemperatureTitle = $leadTemperature->getTemperature() ? $leadTemperature->getTemperature()->getTitle() : 'N/A';

        $notes = 'Changed temperature from ' . $lastTemperatureTitle . ' to ' . $currentTemperatureTitle . '.';

        $activity = new Activity();
        $activity->setLead($lead);
        $activity->setType($type);
        $activity->setOwnerType(ActivityOwnerType::TYPE_LEAD);
        $activity->setDate($leadTemperature->getDate());
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

            /** @var LeadTemperatureRepository $repo */
            $repo = $this->em->getRepository(LeadTemperature::class);

            /** @var LeadTemperature $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LeadTemperature::class), $id);

            if ($entity === null) {
                throw new LeadTemperatureNotFoundException();
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
                throw new LeadTemperatureNotFoundException();
            }

            /** @var LeadTemperatureRepository $repo */
            $repo = $this->em->getRepository(LeadTemperature::class);

            $leadTemperatures = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LeadTemperature::class), $ids);

            if (empty($leadTemperatures)) {
                throw new LeadTemperatureNotFoundException();
            }

            /**
             * @var LeadTemperature $leadTemperature
             */
            foreach ($leadTemperatures as $leadTemperature) {
                $this->em->remove($leadTemperature);
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
            throw new LeadTemperatureNotFoundException();
        }

        /** @var LeadTemperatureRepository $repo */
        $repo = $this->em->getRepository(LeadTemperature::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LeadTemperature::class), $ids);

        if (empty($entities)) {
            throw new LeadTemperatureNotFoundException();
        }

        return $this->getRelatedData(LeadTemperature::class, $entities);
    }
}
