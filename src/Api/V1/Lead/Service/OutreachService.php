<?php

namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\OrganizationNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\OutreachNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\OutreachTypeNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\Contact;
use App\Entity\Lead\Organization;
use App\Entity\Lead\Outreach;
use App\Entity\Lead\OutreachType;
use App\Entity\User;
use App\Repository\Lead\ContactRepository;
use App\Repository\Lead\OrganizationRepository;
use App\Repository\Lead\OutreachRepository;
use App\Repository\Lead\OutreachTypeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class OutreachService
 * @package App\Api\V1\Admin\Service
 */
class OutreachService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var OutreachRepository $repo */
        $repo = $this->em->getRepository(Outreach::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Outreach::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var OutreachRepository $repo */
        $repo = $this->em->getRepository(Outreach::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Outreach::class));
    }

    /**
     * @param $id
     * @return Outreach|null|object
     */
    public function getById($id)
    {
        /** @var OutreachRepository $repo */
        $repo = $this->em->getRepository(Outreach::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Outreach::class), $id);
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

            $typeId = $params['type_id'] ?? 0;

            /** @var OutreachTypeRepository $typeRepo */
            $typeRepo = $this->em->getRepository(OutreachType::class);

            /** @var OutreachType $type */
            $type = $typeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(OutreachType::class), $typeId);

            if ($type === null) {
                throw new OutreachTypeNotFoundException();
            }

            $outreach = new Outreach();
            $outreach->setType($type);

            $notes = $params['notes'] ?? '';
            $outreach->setNotes($notes);

            if (!empty($params['contacts'])) {
                /** @var ContactRepository $contactRepo */
                $contactRepo = $this->em->getRepository(Contact::class);

                $contactIds = array_unique($params['contacts']);
                $contacts = $contactRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Contact::class), $contactIds);

                if (!empty($contacts)) {
                    $outreach->setContacts($contacts);
                } else {
                    $outreach->setContacts(null);
                }
            } else {
                $outreach->setContacts(null);
            }

            if (!empty($params['organization_id'])) {
                /** @var OrganizationRepository $organizationRepo */
                $organizationRepo = $this->em->getRepository(Organization::class);

                /** @var Organization $organization */
                $organization = $organizationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Organization::class), $params['organization_id']);

                if ($organization === null) {
                    throw new OrganizationNotFoundException();
                }

                $outreach->setOrganization($organization);
            } else {
                $outreach->setOrganization(null);
            }

            $date = $params['date'];
            if (!empty($date)) {
                $date = new \DateTime($params['date']);

                $outreach->setDate($date);
            } else {
                $outreach->setDate(null);
            }

            if (!empty($params['participants'])) {
                /** @var UserRepository $userRepo */
                $userRepo = $this->em->getRepository(User::class);

                $userIds = array_unique($params['participants']);
                $users = $userRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(User::class), $userIds);

                if (!empty($users)) {
                    $outreach->setParticipants($users);
                } else {
                    $outreach->setParticipants(null);
                }
            } else {
                $outreach->setParticipants(null);
            }

            $this->validate($outreach, null, ['api_lead_outreach_add']);

            $this->em->persist($outreach);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $outreach->getId();
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

            /** @var OutreachRepository $repo */
            $repo = $this->em->getRepository(Outreach::class);

            /** @var Outreach $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Outreach::class), $id);

            if ($entity === null) {
                throw new OutreachNotFoundException();
            }

            $typeId = $params['type_id'] ?? 0;

            /** @var OutreachTypeRepository $typeRepo */
            $typeRepo = $this->em->getRepository(OutreachType::class);

            /** @var OutreachType $type */
            $type = $typeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(OutreachType::class), $typeId);

            if ($type === null) {
                throw new OutreachTypeNotFoundException();
            }

            $entity->setType($type);

            $notes = $params['notes'] ?? '';
            $entity->setNotes($notes);

            $contacts = $entity->getContacts();
            foreach ($contacts as $contact) {
                $entity->removeContact($contact);
            }

            if (!empty($params['contacts'])) {
                /** @var ContactRepository $contactRepo */
                $contactRepo = $this->em->getRepository(Contact::class);

                $contactIds = array_unique($params['contacts']);
                $contacts = $contactRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Contact::class), $contactIds);

                if (!empty($contacts)) {
                    $entity->setContacts($contacts);
                } else {
                    $entity->setContacts(null);
                }
            } else {
                $entity->setContacts(null);
            }

            if (!empty($params['organization_id'])) {
                /** @var OrganizationRepository $organizationRepo */
                $organizationRepo = $this->em->getRepository(Organization::class);

                /** @var Organization $organization */
                $organization = $organizationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Organization::class), $params['organization_id']);

                if ($organization === null) {
                    throw new OrganizationNotFoundException();
                }

                $entity->setOrganization($organization);
            } else {
                $entity->setOrganization(null);
            }

            $date = $params['date'];
            if (!empty($date)) {
                $date = new \DateTime($params['date']);

                $entity->setDate($date);
            } else {
                $entity->setDate(null);
            }

            $users = $entity->getParticipants();
            foreach ($users as $user) {
                $entity->removeUser($user);
            }

            if (!empty($params['participants'])) {
                /** @var UserRepository $userRepo */
                $userRepo = $this->em->getRepository(User::class);

                $userIds = array_unique($params['participants']);
                $users = $userRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(User::class), $userIds);

                if (!empty($users)) {
                    $entity->setParticipants($users);
                } else {
                    $entity->setParticipants(null);
                }
            } else {
                $entity->setParticipants(null);
            }

            $this->validate($entity, null, ['api_lead_outreach_edit']);

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

            /** @var OutreachRepository $repo */
            $repo = $this->em->getRepository(Outreach::class);

            /** @var Outreach $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Outreach::class), $id);

            if ($entity === null) {
                throw new OutreachNotFoundException();
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
                throw new OutreachNotFoundException();
            }

            /** @var OutreachRepository $repo */
            $repo = $this->em->getRepository(Outreach::class);

            $outreaches = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Outreach::class), $ids);

            if (empty($outreaches)) {
                throw new OutreachNotFoundException();
            }

            /**
             * @var Outreach $outreach
             */
            foreach ($outreaches as $outreach) {
                $this->em->remove($outreach);
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
            throw new OutreachNotFoundException();
        }

        /** @var OutreachRepository $repo */
        $repo = $this->em->getRepository(Outreach::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Outreach::class), $ids);

        if (empty($entities)) {
            throw new OutreachNotFoundException();
        }

        return $this->getRelatedData(Outreach::class, $entities);
    }
}
