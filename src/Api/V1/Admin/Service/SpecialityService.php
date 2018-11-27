<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\PhysicianSpecialityNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\SpecialityNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Speciality;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SpecialityService
 * @package App\Api\V1\Admin\Service
 */
class SpecialityService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Speciality::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Speciality::class)->findAll();
    }

    /**
     * @param $id
     * @return Speciality|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Speciality::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            /**
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            $spaceId = $params['space_id'] ?? 0;

            $space = null;

            if ($spaceId && $spaceId > 0) {
                $space = $this->em->getRepository(Space::class)->find($spaceId);

                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            $speciality = new Speciality();
            $speciality->setTitle($params['title']);
            $speciality->setSpace($space);

            $this->validate($speciality, null, ['api_admin_speciality_add']);

            $this->em->persist($speciality);
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
             * @var Speciality $entity
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            $entity = $this->em->getRepository(Speciality::class)->find($id);

            if ($entity === null) {
                throw new SpecialityNotFoundException();
            }

            $spaceId = $params['space_id'] ?? 0;
            $space   = null;

            if ($spaceId && $spaceId > 0) {
                $space = $this->em->getRepository(Space::class)->find($spaceId);

                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_speciality_edit']);

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

            /** @var Speciality $entity */
            $entity = $this->em->getRepository(Speciality::class)->find($id);

            if (is_null($entity)) {
                throw new SpecialityNotFoundException();
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
                throw new SpecialityNotFoundException();
            }

            $specialities = $this->em->getRepository(Speciality::class)->findByIds($ids);

            if (empty($specialities)) {
                throw new SpecialityNotFoundException();
            }

            /**
             * @var Speciality $speciality
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($specialities as $speciality) {
                $this->em->remove($speciality);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (SpecialityNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
