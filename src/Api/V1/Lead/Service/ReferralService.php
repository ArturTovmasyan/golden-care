<?php
namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\LeadNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\OrganizationNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ReferralNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ReferrerTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\PhoneSinglePrimaryException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\Lead;
use App\Entity\Lead\Organization;
use App\Entity\Lead\Referral;
use App\Entity\Lead\ReferralPhone;
use App\Entity\Lead\ReferrerType;
use App\Repository\Lead\LeadRepository;
use App\Repository\Lead\OrganizationRepository;
use App\Repository\Lead\ReferralPhoneRepository;
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
     * @throws \Exception
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $leadId = $params['lead_id'] ?? 0;

            /** @var LeadRepository $leadRepo */
            $leadRepo = $this->em->getRepository(Lead::class);

            /** @var Lead $lead */
            $lead = $leadRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Lead::class), $leadId);

            if ($lead === null) {
                throw new LeadNotFoundException();
            }

            $typeId = $params['type_id'] ?? 0;

            /** @var ReferrerTypeRepository $typeRepo */
            $typeRepo = $this->em->getRepository(ReferrerType::class);

            /** @var ReferrerType $type */
            $type = $typeRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ReferrerType::class), $typeId);

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
                $organization = $organizationRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Organization::class), $organizationId);

                if ($organization === null) {
                    throw new OrganizationNotFoundException();
                }

                $referral->setOrganization($organization);

                $this->validate($referral, null, ['api_lead_referral_organization_required_add']);
            } else {
                $referral->setOrganization(null);
            }

            if ($type->isRepresentativeRequired()) {
                $emails = !empty($params['emails']) ? $params['emails'] : [];
                $notes = $params['notes'] ?? '';

                $referral->setFirstName($params['first_name']);
                $referral->setLastName($params['last_name']);
                $referral->setNotes($notes);
                $referral->setEmails($emails);
                $referral->setPhones($this->savePhones($referral, $params['phones'] ?? []));

                $this->validate($referral, null, ['api_lead_referral_representative_required_add']);
            } else {
                $referral->setFirstName(null);
                $referral->setLastName(null);
                $referral->setNotes(null);
                $referral->setEmails([]);
                $referral->setPhones($this->savePhones($referral, []));
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
     * @throws \Exception
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
            $lead = $leadRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Lead::class), $leadId);

            if ($lead === null) {
                throw new LeadNotFoundException();
            }

            $typeId = $params['type_id'] ?? 0;

            /** @var ReferrerTypeRepository $typeRepo */
            $typeRepo = $this->em->getRepository(ReferrerType::class);

            /** @var ReferrerType $type */
            $type = $typeRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ReferrerType::class), $typeId);

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
                $organization = $organizationRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Organization::class), $organizationId);

                if ($organization === null) {
                    throw new OrganizationNotFoundException();
                }

                $entity->setOrganization($organization);

                $this->validate($entity, null, ['api_lead_referral_organization_required_edit']);
            } else {
                $entity->setOrganization(null);
            }

            if ($type->isRepresentativeRequired()) {
                $emails = !empty($params['emails']) ? $params['emails'] : [];
                $notes = $params['notes'] ?? '';

                $entity->setFirstName($params['first_name']);
                $entity->setLastName($params['last_name']);
                $entity->setNotes($notes);
                $entity->setEmails($emails);
                $entity->setPhones($this->savePhones($entity, $params['phones'] ?? []));

                $this->validate($entity, null, ['api_lead_referral_representative_required_edit']);
            } else {
                $entity->setFirstName(null);
                $entity->setLastName(null);
                $entity->setNotes(null);
                $entity->setEmails([]);
                $entity->setPhones($this->savePhones($entity, []));
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
     * @param Referral $referral
     * @param array $phones
     * @return array
     */
    public function savePhones(Referral $referral, array $phones = []) : ?array
    {
        if($referral->getId() !== null) {

            /** @var ReferralPhoneRepository $referralPhoneRepo */
            $referralPhoneRepo = $this->em->getRepository(ReferralPhone::class);

            $oldPhones = $referralPhoneRepo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ReferralPhone::class), $referral);

            foreach ($oldPhones as $phone) {
                $this->em->remove($phone);
            }
        }

        $hasPrimary = false;

        $referralPhones = [];

        foreach($phones as $phone) {
            $primary = $phone['primary'] ? (bool) $phone['primary'] : false;

            $referralPhone = new ReferralPhone();
            $referralPhone->setReferral($referral);
            $referralPhone->setCompatibility($phone['compatibility'] ?? null);
            $referralPhone->setType($phone['type']);
            $referralPhone->setNumber($phone['number']);
            $referralPhone->setPrimary($primary);

            if ($referralPhone->isPrimary()) {
                if ($hasPrimary) {
                    throw new PhoneSinglePrimaryException();
                }

                $hasPrimary = true;
            }

            $this->em->persist($referralPhone);

            $referralPhones[] = $referralPhone;
        }

        return $referralPhones;
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
