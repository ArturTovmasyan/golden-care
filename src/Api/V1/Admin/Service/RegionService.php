<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\RegionNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Region;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;

/**
 * Class RegionService
 * @package App\Api\V1\Admin\Service
 */
class RegionService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Region::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Region::class)->list($this->grantService->getCurrentSpace());
    }

    /**
     * @param $id
     * @return Region|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Region::class)->getOne($this->grantService->getCurrentSpace(), $id);
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

            /** @var Space $space */
            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $region = new Region();
            $region->setName($params['name']);
            $region->setDescription($params['description']);
            $region->setShorthand($params['shorthand']);
            $region->setPhone($params['phone']);
            $region->setFax($params['fax']);
            $region->setSpace($space);

            $this->validate($region, null, ['api_admin_region_add']);

            $this->em->persist($region);
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

            /** @var Region $entity */
            $entity = $this->em->getRepository(Region::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($entity === null) {
                throw new RegionNotFoundException();
            }

            $spaceId = $params['space_id'] ?? 0;

            /** @var Space $space */
            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setName($params['name']);
            $entity->setDescription($params['description']);
            $entity->setShorthand($params['shorthand']);
            $entity->setPhone($params['phone']);
            $entity->setFax($params['fax']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_region_edit']);

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

            /** @var Region $entity */
            $entity = $this->em->getRepository(Region::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($entity === null) {
                throw new RegionNotFoundException();
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
                throw new RegionNotFoundException();
            }

            $regions = $this->em->getRepository(Region::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

            if (empty($regions)) {
                throw new RegionNotFoundException();
            }

            /**
             * @var Region $region
             */
            foreach ($regions as $region) {
                $this->em->remove($region);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
