<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\SalutationNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Salutation;
use App\Entity\Space;
use App\Repository\SalutationRepository;
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
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var SalutationRepository $repo */
        $repo = $this->em->getRepository(Salutation::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Salutation::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var SalutationRepository $repo */
        $repo = $this->em->getRepository(Salutation::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Salutation::class));
    }

    /**
     * @param $id
     * @return Salutation|null|object
     */
    public function getById($id)
    {
        /** @var SalutationRepository $repo */
        $repo = $this->em->getRepository(Salutation::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Salutation::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Exception
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

            $salutation = new Salutation();
            $salutation->setTitle($params['title']);
            $salutation->setSpace($space);

            $this->validate($salutation, null, ['api_admin_salutation_add']);

            $this->em->persist($salutation);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $salutation->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
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

            /** @var SalutationRepository $repo */
            $repo = $this->em->getRepository(Salutation::class);

            /** @var Salutation $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Salutation::class), $id);

            if ($entity === null) {
                throw new SalutationNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

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
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var SalutationRepository $repo */
            $repo = $this->em->getRepository(Salutation::class);

            /** @var Salutation $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Salutation::class), $id);

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
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new SalutationNotFoundException();
            }

            /** @var SalutationRepository $repo */
            $repo = $this->em->getRepository(Salutation::class);

            $salutations = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Salutation::class), $ids);

            if (empty($salutations)) {
                throw new SalutationNotFoundException();
            }

            /**
             * @var Salutation $salutation
             */
            foreach ($salutations as $salutation) {
                $this->em->remove($salutation);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
