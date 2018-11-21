<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Facility;
use App\Entity\Physician;
use App\Entity\Resident;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentService
 * @package App\Api\V1\Admin\Service
 */
class ResidentService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Resident::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Resident::class)->findAll();
    }

    /**
     * @param $id
     * @return Facility|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Resident::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $spaceId     = $params['space_id'] ?? 0;
            $physicianId = $params['physician_id'] ?? 0;
            $space       = null;
            $physician   = null;

            if ($spaceId && $spaceId > 0) {
                /** @var Space $space */
                $space = $this->em->getRepository(Space::class)->find($spaceId);


                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            if ($physicianId && $physicianId > 0) {
                /** @var Physician $physician */
                $physician = $this->em->getRepository(Physician::class)->find($physicianId);

                if ($physician === null) {
                    throw new PhysicianNotFoundException();
                }
            }

            /** @todo validate gender **/

            $birthday = \DateTime::createFromFormat('m-d-Y', $params['birthday']);

            $resident = new Resident();
            $resident->setFirstName($params['first_name'] ?? '');
            $resident->setLastName($params['last_name'] ?? '');
            $resident->setMiddleName($params['middle_name'] ?? '');
            $resident->setSpace($space);
            $resident->setPhysician($physician);
            $resident->setGender($params['gender'] ?? 0);
            $resident->setBirthday($birthday);
            $resident->setCreatedAt(new \DateTime());

            $this->validate($resident, null, ['api_admin_resident_add']);

            $this->em->persist($resident);
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

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->find($id);

            if (is_null($resident)) {
                throw new ResidentNotFoundException();
            }

            $spaceId     = $params['space_id'] ?? 0;
            $physicianId = $params['physician_id'] ?? 0;
            $space       = null;
            $physician   = null;

            if ($spaceId && $spaceId > 0) {
                /** @var Space $space */
                $space = $this->em->getRepository(Space::class)->find($spaceId);


                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            if ($physicianId && $physicianId > 0) {
                /** @var Physician $physician */
                $physician = $this->em->getRepository(Physician::class)->find($physicianId);

                if ($physician === null) {
                    throw new PhysicianNotFoundException();
                }
            }

            /** @todo validate gender **/

            $birthday = \DateTime::createFromFormat('m-d-Y', $params['birthday']);

            $resident->setFirstName($params['first_name'] ?? '');
            $resident->setLastName($params['last_name'] ?? '');
            $resident->setMiddleName($params['middle_name'] ?? '');
            $resident->setSpace($space);
            $resident->setPhysician($physician);
            $resident->setGender($params['gender'] ?? 0);
            $resident->setBirthday($birthday);
            $resident->setUpdatedAt(new \DateTime());

            $this->validate($resident, null, ['api_admin_resident_edit']);

            $this->em->persist($resident);
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

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->find($id);

            if (is_null($resident)) {
                throw new ResidentNotFoundException();
            }

            $this->em->remove($resident);
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
                throw new ResidentNotFoundException();
            }

            /** @var Resident $resident */
            $residents = $this->em->getRepository(Resident::class)->findByIds($ids);

            if (empty($residents)) {
                throw new ResidentNotFoundException();
            }

            $this->em->getConnection()->beginTransaction();

            foreach ($residents as $resident) {
                $this->em->remove($resident);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ResidentNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
