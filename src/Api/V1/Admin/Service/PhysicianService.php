<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceHaventAccessToPhysicianException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
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
        $this->em->getRepository(Physician::class)->search($queryBuilder);
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
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->em->getRepository(Physician::class)->find($id);
    }

    /**
     * @param Space $space
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function add(array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $space = $this->em->getRepository(Space::class)->find($params['space_id']);
            $csz   = $this->em->getRepository(CityStateZip::class)->find($params['csz_id']);

            if (is_null($space)) {
                throw new SpaceNotFoundException();
            }

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
            $physician->setSpace($space);

            $this->validate($physician, null, ["api_admin_physician_add"]);

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
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function edit($id, array $params): void
    {
        try {
            /**
             * @var Physician $physician
             */
            $this->em->getConnection()->beginTransaction();

            $space     = $this->em->getRepository(Space::class)->find($params['space_id']);
            $physician = $this->em->getRepository(Physician::class)->find($id);
            $csz       = $this->em->getRepository(CityStateZip::class)->find($params['csz_id']);

            if (is_null($physician)) {
                throw new PhysicianNotFoundException();
            }

            if (is_null($space)) {
                throw new SpaceNotFoundException();
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
            $physician->setSpace($space);

            $this->validate($physician, null, ["api_admin_physician_edit"]);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id): void
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            if (empty($ids)) {
                throw new PhysicianNotFoundException();
            }

            $physicians = $this->em->getRepository(Physician::class)->findByIds($ids);

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