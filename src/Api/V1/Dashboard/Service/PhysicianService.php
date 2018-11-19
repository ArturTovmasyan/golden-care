<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceHaventAccessToPhysicianException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CityStateZip;
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
        $this->em->getRepository(Physician::class)->searchBySpace($queryBuilder, $params[0]);
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

            $csz = $this->em->getRepository(CityStateZip::class)->find($params['csz_id']);

            if (is_null($csz)) {
                throw new CityStateZipNotFoundException();
            }

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
            $physician->setCsz($csz);

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
            $csz       = $this->em->getRepository(CityStateZip::class)->find($params['csz_id']);

            if (is_null($physician)) {
                throw new PhysicianNotFoundException();
            }

            if (is_null($csz)) {
                throw new CityStateZipNotFoundException();
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
            $physician->setCsz($csz);

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

    /**
     * @param array $ids
     * @param Space $space
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids, Space $space): void
    {
        try {
            if (empty($ids)) {
                throw new PhysicianNotFoundException();
            }

            $physicians = $this->em->getRepository(Physician::class)->findByIdsAndSpace($ids, $space);

            if (empty($physicians)) {
                throw new PhysicianNotFoundException();
            }

            /**
             * @var Physician $physician
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($physicians as $physician) {
                $this->em->remove($physician);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (PhysicianNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
