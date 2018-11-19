<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DietNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Diet;
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
        $this->em->getRepository(Diet::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Diet::class)->findAll();
    }

    /**
     * @param $id
     * @return Diet|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Diet::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $diet = new Diet();
            $diet->setTitle($params['title']);
            $diet->setColor($params['color']);

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
            $entity = $this->em->getRepository(Diet::class)->find($id);

            if ($entity === null) {
                throw new DietNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setColor($params['color']);

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
            $entity = $this->em->getRepository(Diet::class)->find($id);

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
            if (empty($ids)) {
                throw new DietNotFoundException();
            }

            $diets = $this->em->getRepository(Diet::class)->findByIds($ids);

            if (empty($diets)) {
                throw new DietNotFoundException();
            }

            /**
             * @var Diet $diet
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($diets as $diet) {
                $this->em->remove($diet);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (DietNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
