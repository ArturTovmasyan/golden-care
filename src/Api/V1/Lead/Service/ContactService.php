<?php
namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\OrganizationNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ContactNotFoundException;
use App\Api\V1\Common\Service\Exception\PhoneSinglePrimaryException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\Organization;
use App\Entity\Lead\Contact;
use App\Entity\Lead\ContactPhone;
use App\Entity\Space;
use App\Repository\Lead\OrganizationRepository;
use App\Repository\Lead\ContactPhoneRepository;
use App\Repository\Lead\ContactRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ContactService
 * @package App\Api\V1\Admin\Service
 */
class ContactService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var ContactRepository $repo */
        $repo = $this->em->getRepository(Contact::class);

        $userId = null;
        if (!empty($params) && isset($params[0]['my']) && !empty($params[0]['user_id'])) {
            $userId = $params[0]['user_id'];
        }

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Contact::class), $queryBuilder, $userId);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();
        $entityGrants = $this->grantService->getCurrentUserEntityGrants(Contact::class);

        /** @var ContactRepository $repo */
        $repo = $this->em->getRepository(Contact::class);

        $userId = null;
        if (!empty($params) && isset($params[0]['my']) && !empty($params[0]['user_id'])) {
            $userId = $params[0]['user_id'];
        }
        return $repo->list($currentSpace, $entityGrants, $userId);
    }

    /**
     * @param $id
     * @return Contact|null|object
     */
    public function getById($id)
    {
        /** @var ContactRepository $repo */
        $repo = $this->em->getRepository(Contact::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Contact::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Exception
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $contact = new Contact();

            if (!empty($params['organization_id'])) {
                /** @var OrganizationRepository $organizationRepo */
                $organizationRepo = $this->em->getRepository(Organization::class);

                /** @var Organization $organization */
                $organization = $organizationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Organization::class), $params['organization_id']);

                if ($organization === null) {
                    throw new OrganizationNotFoundException();
                }

                $contact->setOrganization($organization);
            } else {
                $contact->setOrganization(null);
            }

            $emails = !empty($params['emails']) ? $params['emails'] : [];
            $notes = $params['notes'] ?? '';

            $contact->setSpace($space);
            $contact->setFirstName($params['first_name']);
            $contact->setLastName($params['last_name']);
            $contact->setNotes($notes);
            $contact->setEmails($emails);
            $contact->setPhones($this->savePhones($contact, $params['phones'] ?? []));

            $this->validate($contact, null, ['api_lead_contact_add']);

            $this->em->persist($contact);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $contact->getId();
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
    public function edit($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ContactRepository $repo */
            $repo = $this->em->getRepository(Contact::class);

            /** @var Contact $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Contact::class), $id);

            if ($entity === null) {
                throw new ContactNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
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

            $emails = !empty($params['emails']) ? $params['emails'] : [];
            $notes = $params['notes'] ?? '';

            $entity->setSpace($space);
            $entity->setFirstName($params['first_name']);
            $entity->setLastName($params['last_name']);
            $entity->setNotes($notes);
            $entity->setEmails($emails);
            $entity->setPhones($this->savePhones($entity, $params['phones'] ?? []));

            $this->validate($entity, null, ['api_lead_contact_edit']);

            $this->em->persist($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param Contact $contact
     * @param array $phones
     * @return array
     */
    public function savePhones(Contact $contact, array $phones = []) : ?array
    {
        if($contact->getId() !== null) {

            /** @var ContactPhoneRepository $contactPhoneRepo */
            $contactPhoneRepo = $this->em->getRepository(ContactPhone::class);

            $oldPhones = $contactPhoneRepo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ContactPhone::class), $contact);

            foreach ($oldPhones as $phone) {
                $this->em->remove($phone);
            }
        }

        $hasPrimary = false;

        $contactPhones = [];

        foreach($phones as $phone) {
            $primary = $phone['primary'] ? (bool) $phone['primary'] : false;

            $contactPhone = new ContactPhone();
            $contactPhone->setContact($contact);
            $contactPhone->setCompatibility($phone['compatibility'] ?? null);
            $contactPhone->setType($phone['type']);
            $contactPhone->setNumber($phone['number']);
            $contactPhone->setPrimary($primary);

            if ($contactPhone->isPrimary()) {
                if ($hasPrimary) {
                    throw new PhoneSinglePrimaryException();
                }

                $hasPrimary = true;
            }

            $this->em->persist($contactPhone);

            $contactPhones[] = $contactPhone;
        }

        return $contactPhones;
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ContactRepository $repo */
            $repo = $this->em->getRepository(Contact::class);

            /** @var Contact $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Contact::class), $id);

            if ($entity === null) {
                throw new ContactNotFoundException();
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
                throw new ContactNotFoundException();
            }

            /** @var ContactRepository $repo */
            $repo = $this->em->getRepository(Contact::class);

            $contacts = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Contact::class), $ids);

            if (empty($contacts)) {
                throw new ContactNotFoundException();
            }

            /**
             * @var Contact $contact
             */
            foreach ($contacts as $contact) {
                $this->em->remove($contact);
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
            throw new ContactNotFoundException();
        }

        /** @var ContactRepository $repo */
        $repo = $this->em->getRepository(Contact::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Contact::class), $ids);

        if (empty($entities)) {
            throw new ContactNotFoundException();
        }

        return $this->getRelatedData(Contact::class, $entities);
    }
}
