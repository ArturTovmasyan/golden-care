<?php
namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\OrganizationNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ReferrerTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\PhoneSinglePrimaryException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CityStateZip;
use App\Entity\Lead\Organization;
use App\Entity\Lead\OrganizationPhone;
use App\Entity\Lead\ReferrerType;
use App\Repository\CityStateZipRepository;
use App\Repository\Lead\OrganizationPhoneRepository;
use App\Repository\Lead\OrganizationRepository;
use App\Repository\Lead\ReferrerTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class OrganizationService
 * @package App\Api\V1\Admin\Service
 */
class OrganizationService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var OrganizationRepository $repo */
        $repo = $this->em->getRepository(Organization::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Organization::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var OrganizationRepository $repo */
        $repo = $this->em->getRepository(Organization::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Organization::class));
    }

    /**
     * @param $id
     * @return Organization|null|object
     */
    public function getById($id)
    {
        /** @var OrganizationRepository $repo */
        $repo = $this->em->getRepository(Organization::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Organization::class), $id);
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

            $categoryId = $params['category_id'] ?? 0;

            /** @var ReferrerTypeRepository $categoryRepo */
            $categoryRepo = $this->em->getRepository(ReferrerType::class);

            /** @var ReferrerType $category */
            $category = $categoryRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ReferrerType::class), $categoryId);

            if ($category === null) {
                throw new ReferrerTypeNotFoundException();
            }

            $cszId = $params['csz_id'] ?? 0;

            /** @var CityStateZipRepository $cszRepo */
            $cszRepo = $this->em->getRepository(CityStateZip::class);

            /** @var CityStateZip $csz */
            $csz = $cszRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $cszId);

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            $emails = !empty($params['emails']) ? $params['emails'] : [];

            $organization = new Organization();
            $organization->setTitle($params['title']);
            $organization->setCategory($category);
            $organization->setAddress1($params['address_1']);
            $organization->setAddress2($params['address_2']);
            $organization->setCsz($csz);
            $organization->setWebsiteUrl($params['website_url']);
            $organization->setEmails($emails);
            $organization->setPhones($this->savePhones($organization, $params['phones'] ?? []));

            $this->validate($organization, null, ['api_lead_organization_add']);

            $this->em->persist($organization);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $organization->getId();
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

            /** @var OrganizationRepository $repo */
            $repo = $this->em->getRepository(Organization::class);

            /** @var Organization $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Organization::class), $id);

            if ($entity === null) {
                throw new OrganizationNotFoundException();
            }

            $categoryId = $params['category_id'] ?? 0;

            /** @var ReferrerTypeRepository $categoryRepo */
            $categoryRepo = $this->em->getRepository(ReferrerType::class);

            /** @var ReferrerType $category */
            $category = $categoryRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ReferrerType::class), $categoryId);

            if ($category === null) {
                throw new ReferrerTypeNotFoundException();
            }

            $cszId = $params['csz_id'] ?? 0;

            /** @var CityStateZipRepository $cszRepo */
            $cszRepo = $this->em->getRepository(CityStateZip::class);

            /** @var CityStateZip $csz */
            $csz = $cszRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $cszId);

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            $emails = !empty($params['emails']) ? $params['emails'] : [];

            $entity->setTitle($params['title']);
            $entity->setTitle($params['title']);
            $entity->setCategory($category);
            $entity->setAddress1($params['address_1']);
            $entity->setAddress2($params['address_2']);
            $entity->setCsz($csz);
            $entity->setWebsiteUrl($params['website_url']);
            $entity->setEmails($emails);
            $entity->setPhones($this->savePhones($entity, $params['phones'] ?? []));

            $this->validate($entity, null, ['api_lead_organization_edit']);

            $this->em->persist($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param Organization $organization
     * @param array $phones
     * @return array
     */
    private function savePhones(Organization $organization, array $phones = []) : ?array
    {
        if($organization->getId() !== null) {

            /** @var OrganizationPhoneRepository $organizationPhoneRepo */
            $organizationPhoneRepo = $this->em->getRepository(OrganizationPhone::class);

            $oldPhones = $organizationPhoneRepo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(OrganizationPhone::class), $organization);

            foreach ($oldPhones as $phone) {
                $this->em->remove($phone);
            }
        }

        $hasPrimary = false;

        $organizationPhones = [];

        foreach($phones as $phone) {
            $primary = $phone['primary'] ? (bool) $phone['primary'] : false;
            $smsEnabled = $phone['sms_enabled'] ? (bool) $phone['sms_enabled'] : false;

            $organizationPhone = new OrganizationPhone();
            $organizationPhone->setOrganization($organization);
            $organizationPhone->setCompatibility($phone['compatibility'] ?? null);
            $organizationPhone->setType($phone['type']);
            $organizationPhone->setNumber($phone['number']);
            $organizationPhone->setPrimary($primary);
            $organizationPhone->setSmsEnabled($smsEnabled);

            if ($organizationPhone->isPrimary()) {
                if ($hasPrimary) {
                    throw new PhoneSinglePrimaryException();
                }

                $hasPrimary = true;
            }

            $this->em->persist($organizationPhone);

            $organizationPhones[] = $organizationPhone;
        }

        return $organizationPhones;
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var OrganizationRepository $repo */
            $repo = $this->em->getRepository(Organization::class);

            /** @var Organization $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Organization::class), $id);

            if ($entity === null) {
                throw new OrganizationNotFoundException();
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
                throw new OrganizationNotFoundException();
            }

            /** @var OrganizationRepository $repo */
            $repo = $this->em->getRepository(Organization::class);

            $organizations = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Organization::class), $ids);

            if (empty($organizations)) {
                throw new OrganizationNotFoundException();
            }

            /**
             * @var Organization $organization
             */
            foreach ($organizations as $organization) {
                $this->em->remove($organization);
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
            throw new OrganizationNotFoundException();
        }

        /** @var OrganizationRepository $repo */
        $repo = $this->em->getRepository(Organization::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Organization::class), $ids);

        if (empty($entities)) {
            throw new OrganizationNotFoundException();
        }

        return $this->getRelatedData(Organization::class, $entities);
    }
}
