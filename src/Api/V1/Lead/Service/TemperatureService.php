<?php
namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\TemperatureNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\Temperature;
use App\Entity\Space;
use App\Repository\Lead\TemperatureRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class TemperatureService
 * @package App\Api\V1\Admin\Service
 */
class TemperatureService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var TemperatureRepository $repo */
        $repo = $this->em->getRepository(Temperature::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Temperature::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var TemperatureRepository $repo */
        $repo = $this->em->getRepository(Temperature::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Temperature::class));
    }

    /**
     * @param $id
     * @return Temperature|null|object
     */
    public function getById($id)
    {
        /** @var TemperatureRepository $repo */
        $repo = $this->em->getRepository(Temperature::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Temperature::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $temperature = new Temperature();
            $temperature->setTitle($params['title']);
            $temperature->setValue((int)$params['value']);
            $temperature->setSpace($space);

            $this->validate($temperature, null, ['api_lead_temperature_add']);

            $this->em->persist($temperature);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $temperature->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Throwable
     */
    public function edit($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            /** @var TemperatureRepository $repo */
            $repo = $this->em->getRepository(Temperature::class);

            /** @var Temperature $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Temperature::class), $id);

            if ($entity === null) {
                throw new TemperatureNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setValue((int)$params['value']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_lead_temperature_edit']);

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
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var TemperatureRepository $repo */
            $repo = $this->em->getRepository(Temperature::class);

            /** @var Temperature $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Temperature::class), $id);

            if ($entity === null) {
                throw new TemperatureNotFoundException();
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
     * @throws \Throwable
     */
    public function removeBulk(array $ids)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new TemperatureNotFoundException();
            }

            /** @var TemperatureRepository $repo */
            $repo = $this->em->getRepository(Temperature::class);

            $temperatures = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Temperature::class), $ids);

            if (empty($temperatures)) {
                throw new TemperatureNotFoundException();
            }

            /**
             * @var Temperature $temperature
             */
            foreach ($temperatures as $temperature) {
                $this->em->remove($temperature);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new TemperatureNotFoundException();
        }

        /** @var TemperatureRepository $repo */
        $repo = $this->em->getRepository(Temperature::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Temperature::class), $ids);

        if (empty($entities)) {
            throw new TemperatureNotFoundException();
        }

        return $this->getRelatedData(Temperature::class, $entities);
    }
}
