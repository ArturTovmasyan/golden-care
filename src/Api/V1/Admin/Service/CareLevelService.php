<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CareLevelNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CareLevel;
use App\Repository\CareLevelRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class CareLevelService
 * @package App\Api\V1\Admin\Service
 */
class CareLevelService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return Paginator
     */
    public function getListing(QueryBuilder $queryBuilder, $params) : Paginator
    {
        /** @var CareLevelRepository $careLevelRepo */
        $careLevelRepo = $this->em->getRepository(CareLevel::class);

        return $careLevelRepo->searchAll($queryBuilder);
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

            $careLevel = new CareLevel();
            $careLevel->setTitle($params['title']);
            $careLevel->setDescription($params['description']);

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

            $entity->setTitle($params['title']);
            $entity->setDescription($params['description']);

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
     * @param array $params
     */
    public function removeBulk(array $params)
    {
        $ids = $params['ids'];

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $this->remove($id);
            }
        }
    }
}