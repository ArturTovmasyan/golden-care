<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CareLevelNotFoundException;
use App\Api\V1\Common\Service\Exception\RoleNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CareLevel;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CareLevelService
 * @package App\Api\V1\Admin\Service
 */
class CareLevelService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(CareLevel::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(CareLevel::class)->findAll();
    }

    /**
     * @param $id
     * @return CareLevel|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(CareLevel::class)->find($id);
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

            $careLevel = new CareLevel();
            $careLevel->setTitle($params['title']);
            $careLevel->setDescription($params['description']);
            $careLevel->setSpace($space);

            $this->validate($careLevel, null, ['api_admin_care_level_add']);

            $this->em->persist($careLevel);
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

            /** @var CareLevel $entity */
            $entity = $this->em->getRepository(CareLevel::class)->find($id);

            if ($entity === null) {
                throw new CareLevelNotFoundException();
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
            $entity->setDescription($params['description']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_care_level_edit']);

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

            /** @var CareLevel $entity */
            $entity = $this->em->getRepository(CareLevel::class)->find($id);

            if ($entity === null) {
                throw new CareLevelNotFoundException();
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
    public function removeBulk(array $ids)
    {
        try {
            if (empty($ids)) {
                throw new CareLevelNotFoundException();
            }

            $careLevels = $this->em->getRepository(CareLevel::class)->findByIds($ids);

            if (empty($careLevels)) {
                throw new CareLevelNotFoundException();
            }

            $this->em->getConnection()->beginTransaction();

            /**
             * @var CareLevel $careLevel
             */
            foreach ($careLevels as $careLevel) {
                $this->em->remove($careLevel);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (CareLevelNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
