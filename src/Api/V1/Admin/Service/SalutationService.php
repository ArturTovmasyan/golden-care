<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\SalutationNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Salutation;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SalutationService
 * @package App\Api\V1\Admin\Service
 */
class SalutationService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Salutation::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Salutation::class)->list($this->grantService->getCurrentSpace());
    }

    /**
     * @param $id
     * @return Salutation|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Salutation::class)->getOne($this->grantService->getCurrentSpace(), $id);
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

            $salutation = new Salutation();
            $salutation->setTitle($params['title']);
            $salutation->setSpace($space);

            $this->validate($salutation, null, ['api_admin_salutation_add']);

            $this->em->persist($salutation);
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

            /** @var Salutation $entity */
            $entity = $this->em->getRepository(Salutation::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($entity === null) {
                throw new SalutationNotFoundException();
            }

            $spaceId = $params['space_id'] ?? 0;

            /** @var Space $space */
            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_salutation_edit']);

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

            /** @var Salutation $entity */
            $entity = $this->em->getRepository(Salutation::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($entity === null) {
                throw new SalutationNotFoundException();
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
                throw new SalutationNotFoundException();
            }

            $salutations = $this->em->getRepository(Salutation::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

            if (empty($salutations)) {
                throw new SalutationNotFoundException();
            }

            /**
             * @var Salutation $salutation
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($salutations as $salutation) {
                $this->em->remove($salutation);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (SalutationNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
