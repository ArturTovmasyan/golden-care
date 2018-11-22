<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\PhysicianSpecialityNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\PhysicianSpeciality;
use App\Entity\Space;
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
        $this->em->getRepository(PhysicianSpeciality::class)->search($queryBuilder);
    }

    public function list($params)
    {
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
            $this->em->getConnection()->beginTransaction();

            $spaceId = $params['space_id'] ?? 0;

            $space = null;

            if ($spaceId && $spaceId > 0) {
                /** @var Space $space */
                $space = $this->em->getRepository(Space::class)->find($spaceId);


                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            $physicianSpeciality = new PhysicianSpeciality();
            $physicianSpeciality->setTitle($params['title']);
            $physicianSpeciality->setSpace($space);

            $this->validate($physicianSpeciality, null, ['api_admin_physician_speciality_add']);

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

            $this->em->getConnection()->beginTransaction();

            /** @var PhysicianSpeciality $entity */
            $entity = $this->em->getRepository(PhysicianSpeciality::class)->find($id);

            if ($entity === null) {
                throw new PhysicianSpecialityNotFoundException();
            }

            $spaceId = $params['space_id'] ?? 0;

            $space = null;

            if ($spaceId && $spaceId > 0) {
                /** @var Space $space */
                $space = $this->em->getRepository(Space::class)->find($spaceId);


                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_physician_speciality_edit']);

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

            $physicianSpecialities = $this->em->getRepository(PhysicianSpeciality::class)->findByIds($ids);

            if (empty($physicianSpecialities)) {
                throw new PhysicianSpecialityNotFoundException();
            }

            /**
             * @var PhysicianSpeciality $physicianSpeciality
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($physicianSpecialities as $physicianSpeciality) {
                $this->em->remove($physicianSpeciality);
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
