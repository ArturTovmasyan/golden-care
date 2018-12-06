<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AdditionalDateNotBeBlankException;
use App\Api\V1\Common\Service\Exception\EventDefinitionNotFoundException;
use App\Api\V1\Common\Service\Exception\PhysicianNotBeBlankException;
use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentEventNotFoundException;
use App\Api\V1\Common\Service\Exception\ResponsiblePersonNotBeBlankException;
use App\Api\V1\Common\Service\Exception\ResponsiblePersonNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\EventDefinition;
use App\Entity\Physician;
use App\Entity\Resident;
use App\Entity\ResidentEvent;
use App\Entity\ResponsiblePerson;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentEventService
 * @package App\Api\V1\Admin\Service
 */
class ResidentEventService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('re.resident = :residentId')
            ->setParameter('residentId', $residentId);

        $this->em->getRepository(ResidentEvent::class)->search($queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(ResidentEvent::class)->findBy(['resident' => $residentId]);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentEvent|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResidentEvent::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $residentId = $params['resident_id'] ?? 0;

            $resident = null;

            if ($residentId && $residentId > 0) {
                /** @var Resident $resident */
                $resident = $this->em->getRepository(Resident::class)->find($residentId);

                if ($resident === null) {
                    throw new ResidentNotFoundException();
                }
            }

            $definitionId = $params['definition_id'] ?? 0;

            $definition = null;

            if ($definitionId && $definitionId > 0) {
                /** @var EventDefinition $definition */
                $definition = $this->em->getRepository(EventDefinition::class)->find($definitionId);

                if ($definition === null) {
                    throw new EventDefinitionNotFoundException();
                }
            }

            $physician = null;

            if ($definition && $definition->isPhysician()) {
                $physicianId = $params['physician_id'];

                if ($physicianId && is_numeric($physicianId)) {
                    /** @var Physician $physician */
                    $physician = $this->em->getRepository(Physician::class)->find($physicianId);

                    if ($physician === null) {
                        throw new PhysicianNotFoundException();
                    }
                } else {
                    throw new PhysicianNotBeBlankException();
                }
            }


            $responsiblePerson = null;

            if ($definition && $definition->isResponsiblePerson()) {
                $responsiblePersonId = $params['responsible_person_id'];

                if ($responsiblePersonId && is_numeric($responsiblePersonId)) {
                    /** @var ResponsiblePerson $responsiblePerson */
                    $responsiblePerson = $this->em->getRepository(ResponsiblePerson::class)->find($responsiblePersonId);

                    if ($responsiblePerson === null) {
                        throw new ResponsiblePersonNotFoundException();
                    }
                } else {
                    throw new ResponsiblePersonNotBeBlankException();
                }
            }

            $additionalDate = null;

            if ($definition && $definition->isAdditionalDate()) {

                if (!empty($params['additional_date'])) {
                    $additionalDate = new \DateTime($params['additional_date']);
                } else {
                    throw new AdditionalDateNotBeBlankException();
                }
            }

            $residentEvent = new ResidentEvent();
            $residentEvent->setResident($resident);
            $residentEvent->setDefinition($definition);
            $residentEvent->setPhysician($physician);
            $residentEvent->setResponsiblePerson($responsiblePerson);
            $residentEvent->setAdditionalDate($additionalDate);
            $residentEvent->setNotes($params['notes']);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
            }

            $residentEvent->setDate($date);

            $this->validate($residentEvent, null, ['api_admin_resident_event_add']);

            $this->em->persist($residentEvent);
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

            /** @var ResidentEvent $entity */
            $entity = $this->em->getRepository(ResidentEvent::class)->find($id);

            if ($entity === null) {
                throw new ResidentEventNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            $resident = null;

            if ($residentId && $residentId > 0) {
                /** @var Resident $resident */
                $resident = $this->em->getRepository(Resident::class)->find($residentId);

                if ($resident === null) {
                    throw new ResidentNotFoundException();
                }
            }

            $definition = $entity->getDefinition();

            $physician = null;

            if ($definition && $definition->isPhysician()) {
                $physicianId = $params['physician_id'];

                if ($physicianId && is_numeric($physicianId)) {
                    /** @var Physician $physician */
                    $physician = $this->em->getRepository(Physician::class)->find($physicianId);

                    if ($physician === null) {
                        throw new PhysicianNotFoundException();
                    }
                } else {
                    throw new PhysicianNotBeBlankException();
                }
            }


            $responsiblePerson = null;

            if ($definition && $definition->isResponsiblePerson()) {
                $responsiblePersonId = $params['responsible_person_id'];

                if ($responsiblePersonId && is_numeric($responsiblePersonId)) {
                    /** @var ResponsiblePerson $responsiblePerson */
                    $responsiblePerson = $this->em->getRepository(ResponsiblePerson::class)->find($responsiblePersonId);

                    if ($responsiblePerson === null) {
                        throw new ResponsiblePersonNotFoundException();
                    }
                } else {
                    throw new ResponsiblePersonNotBeBlankException();
                }
            }

            $additionalDate = null;

            if ($definition && $definition->isAdditionalDate()) {

                if (!empty($params['additional_date'])) {
                    $additionalDate = new \DateTime($params['additional_date']);
                } else {
                    throw new AdditionalDateNotBeBlankException();
                }
            }

            $entity->setResident($resident);
            $entity->setPhysician($physician);
            $entity->setResponsiblePerson($responsiblePerson);
            $entity->setAdditionalDate($additionalDate);
            $entity->setNotes($params['notes']);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
            }

            $entity->setDate($date);

            $this->validate($entity, null, ['api_admin_resident_event_edit']);

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

            /** @var ResidentEvent $entity */
            $entity = $this->em->getRepository(ResidentEvent::class)->find($id);

            if ($entity === null) {
                throw new ResidentEventNotFoundException();
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
                throw new ResidentEventNotFoundException();
            }

            $residentEvents = $this->em->getRepository(ResidentEvent::class)->findByIds($ids);

            if (empty($residentEvents)) {
                throw new ResidentEventNotFoundException();
            }

            /**
             * @var ResidentEvent $residentEvent
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($residentEvents as $residentEvent) {
                $this->em->remove($residentEvent);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ResidentEventNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
