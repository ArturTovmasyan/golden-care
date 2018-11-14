<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\RelationshipNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Relationship;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
        $this->em->getRepository(Relationship::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Relationship::class)->findAll();
    }

    /**
     * @param $id
     * @return Relationship|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Relationship::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function add(array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            // save Relationship
            $relationship = new Relationship();
            $relationship->setName($params['name'] ?? null);
            $this->validate($relationship, null, ["api_admin_relationship_add"]);

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
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function edit($id, array $params): void
    {
        try {
            /**
             * @var Relationship $relationship
             */
            $this->em->getConnection()->beginTransaction();

            $relationship = $this->em->getRepository(Relationship::class)->find($id);

            if (is_null($relationship)) {
                throw new RelationshipNotFoundException();
            }

            $relationship->setName($params['name'] ?? null);
            $this->validate($relationship, null, ["api_admin_relationship_edit"]);

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

            $relationship = $this->em->getRepository(Relationship::class)->find($id);

            if (is_null($relationship)) {
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
}