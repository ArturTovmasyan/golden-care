<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DietNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Diet;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DietService
 * @package App\Api\V1\Admin\Service
 */
class DietService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Diet::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Diet::class)->list($this->grantService->getCurrentSpace());
    }

    /**
     * @param $id
     * @return Diet|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Diet::class)->getOne($this->grantService->getCurrentSpace(), $id);
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

            $diet = new Diet();
            $diet->setTitle($params['title']);
            $diet->setColor($params['color']);
            $diet->setSpace($space);

            $this->validate($diet, null, ['api_admin_diet_add']);

            $this->em->persist($diet);
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

            /** @var Diet $entity */
            $entity = $this->em->getRepository(Diet::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($entity === null) {
                throw new DietNotFoundException();
            }

            $spaceId = $params['space_id'] ?? 0;

            /** @var Space $space */
            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setColor($params['color']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_diet_edit']);

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

            /** @var Diet $entity */
            $entity = $this->em->getRepository(Diet::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($entity === null) {
                throw new DietNotFoundException();
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
                throw new DietNotFoundException();
            }

            $diets = $this->em->getRepository(Diet::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

            if (empty($diets)) {
                throw new DietNotFoundException();
            }

            /**
             * @var Diet $diet
             */
            foreach ($diets as $diet) {
                $this->em->remove($diet);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
