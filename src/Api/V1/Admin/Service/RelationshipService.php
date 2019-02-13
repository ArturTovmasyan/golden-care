<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\RelationshipNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Relationship;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;

/**
 * Class RelationshipService
 * @package App\Api\V1\Admin\Service
 */
class RelationshipService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Relationship::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Relationship::class)->list($this->grantService->getCurrentSpace());
    }

    /**
     * @param $id
     * @return Relationship|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Relationship::class)->getOne($this->grantService->getCurrentSpace(), $id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $spaceId = $params['space_id'] ?? 0;

            /** @var Space $space */
            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            // save Relationship
            $relationship = new Relationship();
            $relationship->setTitle($params['title'] ?? null);
            $relationship->setSpace($space);

            $this->validate($relationship, null, ['api_admin_relationship_add']);

            $this->em->persist($relationship);
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
    public function edit($id, array $params): void
    {
        try {
            /**
             * @var Relationship $relationship
             */
            $this->em->getConnection()->beginTransaction();

            $relationship = $this->em->getRepository(Relationship::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($relationship === null) {
                throw new RelationshipNotFoundException();
            }

            $spaceId = $params['space_id'] ?? 0;

            /** @var Space $space */
            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $relationship->setTitle($params['title'] ?? null);
            $relationship->setSpace($space);

            $this->validate($relationship, null, ['api_admin_relationship_edit']);

            $this->em->persist($relationship);
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
    public function remove($id): void
    {
        try {
            /**
             * @var Relationship $relationship
             */
            $this->em->getConnection()->beginTransaction();

            $relationship = $this->em->getRepository(Relationship::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($relationship === null) {
                throw new RelationshipNotFoundException();
            }

            $this->em->remove($relationship);
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
                throw new RelationshipNotFoundException();
            }

            $relationships = $this->em->getRepository(Relationship::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

            if (empty($relationships)) {
                throw new RelationshipNotFoundException();
            }

            /**
             * @var Relationship $relationship
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($relationships as $relationship) {
                $this->em->remove($relationship);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (RelationshipNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
