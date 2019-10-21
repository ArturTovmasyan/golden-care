<?php
namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\ContactNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ContactOrganizationChangedException;
use App\Api\V1\Common\Service\Exception\Lead\LeadAlreadyJoinedInReferralException;
use App\Api\V1\Common\Service\Exception\Lead\LeadNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\OrganizationNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ReferralNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ReferrerTypeNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\Contact;
use App\Entity\Lead\Lead;
use App\Entity\Lead\Organization;
use App\Entity\Lead\Referral;
use App\Entity\Lead\ReferrerType;
use App\Repository\Lead\ContactRepository;
use App\Repository\Lead\LeadRepository;
use App\Repository\Lead\OrganizationRepository;
use App\Repository\Lead\ReferralRepository;
use App\Repository\Lead\ReferrerTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ReferralService
 * @package App\Api\V1\Admin\Service
 */
class ReferralService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var ReferralRepository $repo */
        $repo = $this->em->getRepository(Referral::class);

        $organizationId = null;
        if (!empty($params) && !empty($params[0]['organization_id'])) {
            $organizationId = $params[0]['organization_id'];
        }

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Referral::class), $queryBuilder, $organizationId);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();
        $entityGrants = $this->grantService->getCurrentUserEntityGrants(Referral::class);

        /** @var ReferralRepository $repo */
        $repo = $this->em->getRepository(Referral::class);

        if (!empty($params) && !empty($params[0]['organization_id'])) {
            $organizationId = $params[0]['organization_id'];

            return $repo->getBy($currentSpace, $entityGrants, $organizationId);
        }

        return $repo->list($currentSpace, $entityGrants);
    }

    /**
     * @param $id
     * @return Referral|null|object
     */
    public function getById($id)
    {
        /** @var ReferralRepository $repo */
        $repo = $this->em->getRepository(Referral::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Referral::class), $id);
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

            /** @var ReferralRepository $repo */
            $repo = $this->em->getRepository(Referral::class);

            $referrals = $repo->getByLeadWithoutCurrent($currentSpace, $this->grantService->getCurrentUserEntityGrants(Referral::class), $leadId);
            if (\count($referrals) > 0) {
                throw new LeadAlreadyJoinedInReferralException();
            }

            $typeId = $params['type_id'] ?? 0;

            /** @var ReferrerTypeRepository $typeRepo */
            $typeRepo = $this->em->getRepository(ReferrerType::class);

            /** @var ReferrerType $type */
            $type = $typeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ReferrerType::class), $typeId);

            if ($type === null) {
                throw new ReferrerTypeNotFoundException();
            }

            $referral = new Referral();
            $referral->setLead($lead);
            $referral->setType($type);

            if ($type->isOrganizationRequired()) {

                $organizationId = $params['organization_id'] ?? 0;

                /** @var OrganizationRepository $organizationRepo */
                $organizationRepo = $this->em->getRepository(Organization::class);

                /** @var Organization $organization */
                $organization = $organizationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Organization::class), $organizationId);

                if ($organization === null) {
                    throw new OrganizationNotFoundException();
                }

                $referral->setOrganization($organization);

                $this->validate($referral, null, ['api_lead_referral_organization_required_add']);
            } else {
                $referral->setOrganization(null);
            }

            if ($type->isRepresentativeRequired()) {

                $notes = $params['notes'] ?? '';
                $contactId = $params['contact_id'] ?? 0;

                /** @var ContactRepository $contactRepo */
                $contactRepo = $this->em->getRepository(Contact::class);

                /** @var Contact $contact */
                $contact = $contactRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Contact::class), $contactId);

                if ($contact === null) {
                    throw new ContactNotFoundException();
                }

                if ($referral->getOrganization() !== null && $contact->getOrganization() !== null && $referral->getOrganization()->getId() !== $contact->getOrganization()->getId()) {
                    throw new ContactOrganizationChangedException();
                }

                $referral->setContact($contact);
                $referral->setNotes($notes);

                $this->validate($referral, null, ['api_lead_referral_representative_required_add']);
            } else {
                $referral->setContact(null);
                $referral->setNotes('');
            }

            $this->em->persist($referral);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $referral->getId();
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

            /** @var ReferralRepository $repo */
            $repo = $this->em->getRepository(Referral::class);

            /** @var Referral $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Referral::class), $id);

            if ($entity === null) {
                throw new ReferralNotFoundException();
            }

            $leadId = $params['lead_id'] ?? 0;

            /** @var LeadRepository $leadRepo */
            $leadRepo = $this->em->getRepository(Lead::class);

            /** @var Lead $lead */
            $lead = $leadRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $leadId);

            if ($lead === null) {
                throw new LeadNotFoundException();
            }

            $referrals = $repo->getByLeadWithoutCurrent($currentSpace, $this->grantService->getCurrentUserEntityGrants(Referral::class), $leadId, $id);
            if (\count($referrals) > 0) {
                throw new LeadAlreadyJoinedInReferralException();
            }

            $typeId = $params['type_id'] ?? 0;

            /** @var ReferrerTypeRepository $typeRepo */
            $typeRepo = $this->em->getRepository(ReferrerType::class);

            /** @var ReferrerType $type */
            $type = $typeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ReferrerType::class), $typeId);

            if ($type === null) {
                throw new ReferrerTypeNotFoundException();
            }

            $entity->setLead($lead);
            $entity->setType($type);

            if ($type->isOrganizationRequired()) {

                $organizationId = $params['organization_id'] ?? 0;

                /** @var OrganizationRepository $organizationRepo */
                $organizationRepo = $this->em->getRepository(Organization::class);

                /** @var Organization $organization */
                $organization = $organizationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Organization::class), $organizationId);

                if ($organization === null) {
                    throw new OrganizationNotFoundException();
                }

                $entity->setOrganization($organization);

                $this->validate($entity, null, ['api_lead_referral_organization_required_edit']);
            } else {
                $entity->setOrganization(null);
            }

            if ($type->isRepresentativeRequired()) {

                $notes = $params['notes'] ?? '';
                $contactId = $params['contact_id'] ?? 0;

                /** @var ContactRepository $contactRepo */
                $contactRepo = $this->em->getRepository(Contact::class);

                /** @var Contact $contact */
                $contact = $contactRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Contact::class), $contactId);

                if ($contact === null) {
                    throw new ContactNotFoundException();
                }

                if ($entity->getOrganization() !== null && $contact->getOrganization() !== null && $entity->getOrganization()->getId() !== $contact->getOrganization()->getId()) {
                    throw new ContactOrganizationChangedException();
                }

                $entity->setContact($contact);
                $entity->setNotes($notes);

                $this->validate($entity, null, ['api_lead_referral_representative_required_edit']);
            } else {
                $entity->setContact(null);
                $entity->setNotes('');
            }

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

            /** @var ReferralRepository $repo */
            $repo = $this->em->getRepository(Referral::class);

            /** @var Referral $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Referral::class), $id);

            if ($entity === null) {
                throw new ReferralNotFoundException();
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
                throw new ReferralNotFoundException();
            }

            /** @var ReferralRepository $repo */
            $repo = $this->em->getRepository(Referral::class);

            $referrals = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Referral::class), $ids);

            if (empty($referrals)) {
                throw new ReferralNotFoundException();
            }

            /**
             * @var Referral $referral
             */
            foreach ($referrals as $referral) {
                $this->em->remove($referral);
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
            throw new ReferralNotFoundException();
        }

        /** @var ReferralRepository $repo */
        $repo = $this->em->getRepository(Referral::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Referral::class), $ids);

        if (empty($entities)) {
            throw new ReferralNotFoundException();
        }

        return $this->getRelatedData(Referral::class, $entities);
    }
}
