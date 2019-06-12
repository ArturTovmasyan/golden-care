<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ChangeLogNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ChangeLog;
use App\Repository\ChangeLogRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ChangeLogService
 * @package App\Api\V1\Admin\Service
 */
class ChangeLogService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var ChangeLogRepository $repo */
        $repo = $this->em->getRepository(ChangeLog::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ChangeLog::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var ChangeLogRepository $repo */
        $repo = $this->em->getRepository(ChangeLog::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ChangeLog::class));
    }

    /**
     * @param $id
     * @return ChangeLog|null|object
     */
    public function getById($id)
    {
        /** @var ChangeLogRepository $repo */
        $repo = $this->em->getRepository(ChangeLog::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ChangeLog::class), $id);
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ChangeLogRepository $repo */
            $repo = $this->em->getRepository(ChangeLog::class);

            /** @var ChangeLog $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ChangeLog::class), $id);

            if ($entity === null) {
                throw new ChangeLogNotFoundException();
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
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ChangeLogNotFoundException();
            }

            /** @var ChangeLogRepository $repo */
            $repo = $this->em->getRepository(ChangeLog::class);

            $changeLogs = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ChangeLog::class), $ids);

            if (empty($changeLogs)) {
                throw new ChangeLogNotFoundException();
            }

            /**
             * @var ChangeLog $changeLog
             */
            foreach ($changeLogs as $changeLog) {
                $this->em->remove($changeLog);
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
            throw new ChangeLogNotFoundException();
        }

        /** @var ChangeLogRepository $repo */
        $repo = $this->em->getRepository(ChangeLog::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ChangeLog::class), $ids);

        if (empty($entities)) {
            throw new ChangeLogNotFoundException();
        }

        return $this->getRelatedData(ChangeLog::class, $entities);
    }
}
