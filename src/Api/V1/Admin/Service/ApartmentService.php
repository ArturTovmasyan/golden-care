<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\ApartmentNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CityStateZip;
use App\Entity\Apartment;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ApartmentService
 * @package App\Api\V1\Admin\Service
 */
class ApartmentService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Apartment::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Apartment::class)->findAll();
    }

    /**
     * @param $id
     * @return Apartment|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Apartment::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $spaceId = $params['space_id'] ?? 0;
            $cszId = $params['csz_id'] ?? 0;

            /** @var Space $space */
            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            /** @var CityStateZip $csz */
            $csz = $this->em->getRepository(CityStateZip::class)->find($cszId);

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            $apartment = new Apartment();
            $apartment->setName($params['name']);
            $apartment->setDescription($params['description']);
            $apartment->setShorthand($params['shorthand']);
            $apartment->setPhone($params['phone']);
            $apartment->setFax($params['fax']);
            $apartment->setAddress($params['address']);
            $apartment->setLicense($params['license']);
            $apartment->setCsz($csz);
            $apartment->setLicenseCapacity((int)$params['license_capacity']);
            $apartment->setCapacity((int)$params['capacity']);
            $apartment->setSpace($space);

            $this->validate($apartment, null, ['api_admin_apartment_add']);

            $this->em->persist($apartment);
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
     * @throws \Exception
     */
    public function edit($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            /** @var Apartment $entity */
            $entity = $this->em->getRepository(Apartment::class)->find($id);

            if ($entity === null) {
                throw new ApartmentNotFoundException();
            }

            $spaceId = $params['space_id'] ?? 0;
            $cszId = $params['csz_id'] ?? 0;

            /** @var Space $space */
            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            /** @var CityStateZip $csz */
            $csz = $this->em->getRepository(CityStateZip::class)->find($cszId);

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            $entity->setName($params['name']);
            $entity->setDescription($params['description']);
            $entity->setShorthand($params['shorthand']);
            $entity->setPhone($params['phone']);
            $entity->setFax($params['fax']);
            $entity->setAddress($params['address']);
            $entity->setLicense($params['license']);
            $entity->setCsz($csz);
            $entity->setLicenseCapacity((int)$params['license_capacity']);
            $entity->setCapacity((int)$params['capacity']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_apartment_edit']);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Apartment $entity */
            $entity = $this->em->getRepository(Apartment::class)->find($id);

            if ($entity === null) {
                throw new ApartmentNotFoundException();
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
            if (empty($ids)) {
                throw new ApartmentNotFoundException();
            }

            $apartments = $this->em->getRepository(Apartment::class)->findByIds($ids);

            if (empty($apartments)) {
                throw new ApartmentNotFoundException();
            }

            /**
             * @var Apartment $apartment
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($apartments as $apartment) {
                $this->em->remove($apartment);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ApartmentNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
