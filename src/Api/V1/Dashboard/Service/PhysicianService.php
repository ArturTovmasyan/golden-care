<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceHaventAccessToPhysicianException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Physician;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PhysicianService
 * @package App\Api\V1\Dashboard\Service
 */
class PhysicianService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Physician::class)->findBySpace($queryBuilder, $params[0]);
    }

    /**
     * @param $params
     * @return array|object[]
     */
    public function list($params)
    {
        return $this->em->getRepository(Physician::class)->findAll();
    }

    /**
     * @param Space $space
     * @param $id
     * @return mixed
     */
    public function getBySpaceAndId(Space $space, $id)
    {
        return $this->em->getRepository(Physician::class)->findBySpaceAndId($space, $id);
    }

    /**
     * @param Space $space
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function add(Space $space, array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            // save physician
            $physician = new Physician();
            $physician->setFirstName($params['first_name'] ?? '');
            $physician->setMiddleName($params['middle_name'] ?? '');
            $physician->setLastName($params['last_name'] ?? '');
            $physician->setAddress1($params['address_1'] ?? '');
            $physician->setAddress2($params['address_2'] ?? '');
            $physician->setOfficePhone($params['office_phone'] ?? '');
            $physician->setFax($params['fax'] ?? '');
            $physician->setEmergencyPhone($params['emergency_phone'] ?? '');
            $physician->setEmail($params['email'] ?? '');
            $physician->setWebsiteUrl($params['website_url'] ?? '');
            $physician->setSpace($space);

            $this->validate($physician, null, ["api_dashboard_physician_add"]);

            $this->em->persist($physician);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @param Space $space
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function edit($id, Space $space, array $params): void
    {
        try {
            /**
             * @var Physician $physician
             */
            $this->em->getConnection()->beginTransaction();

            $physician = $this->em->getRepository(Physician::class)->find($id);

            if (is_null($physician)) {
                throw new PhysicianNotFoundException();
            }

            // edit physician
            $physician->setFirstName($params['first_name'] ?? '');
            $physician->setMiddleName($params['middle_name'] ?? '');
            $physician->setLastName($params['last_name'] ?? '');
            $physician->setAddress1($params['address_1'] ?? '');
            $physician->setAddress2($params['address_2'] ?? '');
            $physician->setOfficePhone($params['office_phone'] ?? '');
            $physician->setFax($params['fax'] ?? '');
            $physician->setEmergencyPhone($params['emergency_phone'] ?? '');
            $physician->setEmail($params['email'] ?? '');
            $physician->setWebsiteUrl($params['website_url'] ?? '');

            $this->validate($physician, null, ["api_dashboard_physician_edit"]);

            $this->em->persist($physician);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }


    /**
     * @param $id
     * @param Space $space
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id, Space $space): void
    {
        try {
            /**
             * @var Physician $physician
             */
            $this->em->getConnection()->beginTransaction();

            $physician = $this->em->getRepository(Physician::class)->find($id);

            if (is_null($physician)) {
                throw new PhysicianNotFoundException();
            }

            $physicianSpace = $physician->getSpace();

            if (is_null($physicianSpace) || $physicianSpace->getId() != $space->getId()) {
                throw new SpaceHaventAccessToPhysicianException();
            }

            $this->em->remove($physician);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
