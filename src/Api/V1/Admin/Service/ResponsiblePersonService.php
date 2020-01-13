<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\PhoneSinglePrimaryException;
use App\Api\V1\Common\Service\Exception\ResponsiblePersonNotFoundException;
use App\Api\V1\Common\Service\Exception\SalutationNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CityStateZip;
use App\Entity\ResponsiblePerson;
use App\Entity\ResponsiblePersonPhone;
use App\Entity\Salutation;
use App\Entity\Space;
use App\Repository\CityStateZipRepository;
use App\Repository\ResponsiblePersonPhoneRepository;
use App\Repository\ResponsiblePersonRepository;
use App\Repository\SalutationRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResponsiblePersonService
 * @package App\Api\V1\Admin\Service
 */
class ResponsiblePersonService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var ResponsiblePersonRepository $repo */
        $repo = $this->em->getRepository(ResponsiblePerson::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePerson::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var ResponsiblePersonRepository $repo */
        $repo = $this->em->getRepository(ResponsiblePerson::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePerson::class));
    }

    /**
     * @param $id
     * @return ResponsiblePerson|null|object
     */
    public function getById($id)
    {
        /** @var ResponsiblePersonRepository $repo */
        $repo = $this->em->getRepository(ResponsiblePerson::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePerson::class), $id);
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
            /**
             * @var Space $space
             * @var CityStateZip $csz
             * @var Salutation $salutation
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $cszId = $params['csz_id'] ?? 0;
            $salutationId = $params['salutation_id'] ?? 0;

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            /** @var CityStateZipRepository $cszRepo */
            $cszRepo = $this->em->getRepository(CityStateZip::class);

            /** @var CityStateZip $csz */
            $csz = $cszRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $cszId);

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            /** @var SalutationRepository $salutationRepo */
            $salutationRepo = $this->em->getRepository(Salutation::class);

            $salutation = $salutationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Salutation::class), $salutationId);

            if ($salutation === null) {
                throw new SalutationNotFoundException();
            }

            $responsiblePerson = new ResponsiblePerson();
            $responsiblePerson->setSpace($space);
            $responsiblePerson->setCsz($csz);
            $responsiblePerson->setSalutation($salutation);
            $responsiblePerson->setFirstName($params['first_name'] ?? '');
            $responsiblePerson->setLastName($params['last_name'] ?? '');
            $responsiblePerson->setMiddleName($params['middle_name'] ?? '');
            $responsiblePerson->setAddress1($params['address_1'] ?? '');
            $responsiblePerson->setAddress2($params['address_2'] ?? '');
            $responsiblePerson->setEmail($params['email'] ?? '');
            $responsiblePerson->setPhones($this->savePhones($responsiblePerson, $params['phones'] ?? []));

            $this->validate($responsiblePerson, null, ['api_admin_responsible_person_add']);
            $this->em->persist($responsiblePerson);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $responsiblePerson->getId();
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
            /**
             * @var Space $space
             * @var CityStateZip $csz
             * @var ResponsiblePerson $responsiblePerson
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $cszId = $params['csz_id'] ?? 0;
            $salutationId = $params['salutation_id'] ?? 0;

            /** @var ResponsiblePersonRepository $repo */
            $repo = $this->em->getRepository(ResponsiblePerson::class);

            $responsiblePerson = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResponsiblePerson::class), $id);

            if ($responsiblePerson === null) {
                throw new ResponsiblePersonNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            /** @var CityStateZipRepository $cszRepo */
            $cszRepo = $this->em->getRepository(CityStateZip::class);

            /** @var CityStateZip $csz */
            $csz = $cszRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $cszId);

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            /** @var SalutationRepository $salutationRepo */
            $salutationRepo = $this->em->getRepository(Salutation::class);

            $salutation = $salutationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Salutation::class), $salutationId);

            if ($salutation === null) {
                throw new SalutationNotFoundException();
            }

            $responsiblePerson->setSpace($space);
            $responsiblePerson->setCsz($csz);
            $responsiblePerson->setSalutation($salutation);
            $responsiblePerson->setFirstName($params['first_name'] ?? '');
            $responsiblePerson->setLastName($params['last_name'] ?? '');
            $responsiblePerson->setMiddleName($params['middle_name'] ?? '');
            $responsiblePerson->setAddress1($params['address_1'] ?? '');
            $responsiblePerson->setAddress2($params['address_2'] ?? '');
            $responsiblePerson->setEmail($params['email'] ?? '');

            $responsiblePerson->setPhones($this->savePhones($responsiblePerson, $params['phones'] ?? []));

            $this->validate($responsiblePerson, null, ['api_admin_responsible_person_edit']);
            $this->em->persist($responsiblePerson);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param ResponsiblePerson $responsiblePerson
     * @param array $phones
     * @return array
     */
    private function savePhones(ResponsiblePerson $responsiblePerson, array $phones = []): ?array
    {
        if ($responsiblePerson->getId() !== null) {
            /** @var ResponsiblePersonPhoneRepository $responsiblePersonPhoneRepo */
            $responsiblePersonPhoneRepo = $this->em->getRepository(ResponsiblePersonPhone::class);

            $oldPhones = $responsiblePersonPhoneRepo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonPhone::class), $responsiblePerson);

            foreach ($oldPhones as $phone) {
                $this->em->remove($phone);
            }
        }

        $hasPrimary = false;

        $responsiblePersonPhones = [];

        foreach ($phones as $phone) {
            $primary = $phone['primary'] ? (bool)$phone['primary'] : false;
            $smsEnabled = $phone['sms_enabled'] ? (bool)$phone['sms_enabled'] : false;

            $responsiblePersonPhone = new ResponsiblePersonPhone();
            $responsiblePersonPhone->setResponsiblePerson($responsiblePerson);
            $responsiblePersonPhone->setCompatibility($phone['compatibility'] ?? null);
            $responsiblePersonPhone->setType($phone['type']);
            $responsiblePersonPhone->setNumber($phone['number']);
            $responsiblePersonPhone->setPrimary($primary);
            $responsiblePersonPhone->setSmsEnabled($smsEnabled);
            $responsiblePersonPhone->setExtension($phone['extension']);

            if ($responsiblePersonPhone->isPrimary()) {
                if ($hasPrimary) {
                    throw new PhoneSinglePrimaryException();
                }

                $hasPrimary = true;
            }

            $this->em->persist($responsiblePersonPhone);

            $responsiblePersonPhones[] = $responsiblePersonPhone;
        }

        return $responsiblePersonPhones;
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResponsiblePersonRepository $repo */
            $repo = $this->em->getRepository(ResponsiblePerson::class);

            /** @var ResponsiblePerson $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePerson::class), $id);

            if ($entity === null) {
                throw new ResponsiblePersonNotFoundException();
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
                throw new ResponsiblePersonNotFoundException();
            }

            /** @var ResponsiblePersonRepository $repo */
            $repo = $this->em->getRepository(ResponsiblePerson::class);

            $responsiblePersons = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePerson::class), $ids);

            if (empty($responsiblePersons)) {
                throw new ResponsiblePersonNotFoundException();
            }

            /**
             * @var ResponsiblePerson $responsiblePerson
             */
            foreach ($responsiblePersons as $responsiblePerson) {
                $this->em->remove($responsiblePerson);
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
            throw new ResponsiblePersonNotFoundException();
        }

        /** @var ResponsiblePersonRepository $repo */
        $repo = $this->em->getRepository(ResponsiblePerson::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePerson::class), $ids);

        if (empty($entities)) {
            throw new ResponsiblePersonNotFoundException();
        }

        return $this->getRelatedData(ResponsiblePerson::class, $entities);
    }
}
