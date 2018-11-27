<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DuplicateSpecialityRequestException;
use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Api\V1\Common\Service\Exception\PhysicianSpecialityNotFoundException;
use App\Api\V1\Common\Service\Exception\SpecialityNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Physician;
use App\Entity\PhysicianSpeciality;
use App\Entity\Speciality;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PhysicianSpecialityService
 * @package App\Api\V1\Admin\Service
 */
class PhysicianSpecialityService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        if (!empty($params) && !empty($params[0]['physician_id'])) {
            $physicianId = $params[0]['physician_id'];

            $this->em->getRepository(PhysicianSpeciality::class)->findBy(['physician' => $physicianId]);
        } else {
            $this->em->getRepository(PhysicianSpeciality::class)->search($queryBuilder);
        }
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['physician_id'])) {
            $physicianId = $params[0]['physician_id'];

            return $this->em->getRepository(PhysicianSpeciality::class)->findBy(['physician' => $physicianId]);
        }

        return $this->em->getRepository(PhysicianSpeciality::class)->findAll();
    }

    /**
     * @param $id
     * @return PhysicianSpeciality|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(PhysicianSpeciality::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            /**
             * @var Physician $physician
             * @var Speciality $speciality
             */
            $this->em->getConnection()->beginTransaction();

            $physicianId = $params['physician_id'] ?? 0;

            $physician = null;

            if ($physicianId && $physicianId > 0) {
                $physician = $this->em->getRepository(Physician::class)->find($physicianId);

                if (is_null($physician)) {
                    throw new PhysicianNotFoundException();
                }
            }

            $specialityId  = $params['speciality_id'];
            $newSpeciality = $params['speciality'];

            if ((empty($specialityId) && empty($newSpeciality)) || (!empty($specialityId) && !empty($newSpeciality))) {
                throw new DuplicateSpecialityRequestException();
            }

            $speciality = null;

            if (!empty($newSpeciality)) {
                $speciality = new Speciality();
                $speciality->setTitle($newSpeciality['title'] ?? '');
            }

            if (!empty($specialityId)) {
                $speciality = $this->em->getRepository(Speciality::class)->find($specialityId);

                if (is_null($speciality)) {
                    throw new SpecialityNotFoundException();
                }
            }

            $physicianSpeciality = new PhysicianSpeciality();
            $physicianSpeciality->setPhysician($physician);
            $physicianSpeciality->setSpeciality($speciality);

            $this->validate($physicianSpeciality, null, ['api_admin_physician_speciality_add']);

            $this->em->persist($speciality);
            $this->em->persist($physicianSpeciality);
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
            /**
             * @var PhysicianSpeciality $entity
             * @var Speciality $speciality
             * @var Physician $physician
             */
            $this->em->getConnection()->beginTransaction();

            $entity = $this->em->getRepository(PhysicianSpeciality::class)->find($id);

            if (is_null($entity)) {
                throw new PhysicianSpecialityNotFoundException();
            }

            $physicianId = $params['physician_id'] ?? 0;
            $physician   = null;

            if ($physicianId && $physicianId > 0) {
                $physician = $this->em->getRepository(Physician::class)->find($physicianId);

                if ($physician === null) {
                    throw new PhysicianNotFoundException();
                }
            }

            $specialityId  = $params['speciality_id'];
            $newSpeciality = $params['speciality'];

            if ((empty($specialityId) && empty($newSpeciality)) || (!empty($specialityId) && !empty($newSpeciality))) {
                throw new DuplicateSpecialityRequestException();
            }

            $speciality = null;

            if (!empty($newSpeciality)) {
                $speciality = new Speciality();
                $speciality->setTitle($newSpeciality['title'] ?? '');
            }

            if (!empty($specialityId)) {
                $speciality = $this->em->getRepository(Speciality::class)->find($specialityId);

                if (is_null($speciality)) {
                    throw new SpecialityNotFoundException();
                }
            }

            $entity->setPhysician($physician);
            $entity->setSpeciality($speciality);

            $this->validate($entity, null, ['api_admin_physician_speciality_edit']);

            $this->em->persist($speciality);
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

            /** @var PhysicianSpeciality $entity */
            $entity = $this->em->getRepository(PhysicianSpeciality::class)->find($id);

            if ($entity === null) {
                throw new PhysicianSpecialityNotFoundException();
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
                throw new PhysicianSpecialityNotFoundException();
            }

            $entities = $this->em->getRepository(PhysicianSpeciality::class)->findByIds($ids);

            if (empty($entities)) {
                throw new PhysicianSpecialityNotFoundException();
            }

            /**
             * @var PhysicianSpeciality $entity
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($entities as $entity) {
                $this->em->remove($entity);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (PhysicianSpecialityNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
