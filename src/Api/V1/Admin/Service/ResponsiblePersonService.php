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
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(ResponsiblePerson::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(ResponsiblePerson::class)->list($this->grantService->getCurrentSpace());
    }

    /**
     * @param $id
     * @return ResponsiblePerson|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResponsiblePerson::class)->getOne($this->grantService->getCurrentSpace(), $id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : ?int
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

            $spaceId      = $params['space_id'] ?? 0;
            $cszId        = $params['csz_id'] ?? 0;
            $salutationId = $params['salutation_id'] ?? 0;

            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $csz = $this->em->getRepository(CityStateZip::class)->getOne($currentSpace, $cszId);

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            $salutation = $this->em->getRepository(Salutation::class)->getOne($currentSpace, $salutationId);

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
            $responsiblePerson->setFinancially($params['financially'] ?? false);
            $responsiblePerson->setEmergency($params['emergency'] ?? false);
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
     * @throws \Exception
     */
    public function edit($id, array $params) : void
    {
        try {
            /**
             * @var Space $space
             * @var CityStateZip $csz
             * @var ResponsiblePerson $responsiblePerson
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $spaceId      = $params['space_id'] ?? 0;
            $cszId        = $params['csz_id'] ?? 0;
            $salutationId = $params['salutation_id'] ?? 0;

            $responsiblePerson = $this->em->getRepository(ResponsiblePerson::class)->getOne($currentSpace, $id);

            if ($responsiblePerson === null) {
                throw new ResponsiblePersonNotFoundException();
            }

            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $csz = $this->em->getRepository(CityStateZip::class)->getOne($currentSpace, $cszId);

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            $salutation = $this->em->getRepository(Salutation::class)->getOne($currentSpace, $salutationId);

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
            $responsiblePerson->setFinancially($params['financially'] ?? false);
            $responsiblePerson->setEmergency($params['emergency'] ?? false);

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
    private function savePhones(ResponsiblePerson $responsiblePerson, array $phones = [])
    {
        if($responsiblePerson->getId() !== null) {
            /**
             * @var ResponsiblePerson $phone
             */
            $oldPhones = $this->em->getRepository(ResponsiblePersonPhone::class)->getBy($this->grantService->getCurrentSpace(), $responsiblePerson);

            foreach ($oldPhones as $phone) {
                $this->em->remove($phone);
            }
        }

        $hasPrimary = false;

        $responsiblePersonPhones = [];

        foreach($phones as $phone) {
            $primary = $phone['primary'] ? (bool) $phone['primary'] : false;
            $smsEnabled = $phone['sms_enabled'] ? (bool) $phone['sms_enabled'] : false;

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResponsiblePerson $entity */
            $entity = $this->em->getRepository(ResponsiblePerson::class)->getOne($this->grantService->getCurrentSpace(), $id);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResponsiblePersonNotFoundException();
            }

            $responsiblePersons = $this->em->getRepository(ResponsiblePerson::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

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
}
