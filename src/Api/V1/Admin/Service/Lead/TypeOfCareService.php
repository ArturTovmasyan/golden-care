<?php
namespace App\Api\V1\Admin\Service\Lead;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\TypeOfCareNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\TypeOfCare;
use App\Entity\Space;
use App\Repository\lead\TypeOfCareRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class TypeOfCareService
 * @package App\Api\V1\Admin\Service
 */
class TypeOfCareService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var TypeOfCareRepository $repo */
        $repo = $this->em->getRepository(TypeOfCare::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(TypeOfCare::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var TypeOfCareRepository $repo */
        $repo = $this->em->getRepository(TypeOfCare::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(TypeOfCare::class));
    }

    /**
     * @param $id
     * @return TypeOfCare|null|object
     */
    public function getById($id)
    {
        /** @var TypeOfCareRepository $repo */
        $repo = $this->em->getRepository(TypeOfCare::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(TypeOfCare::class), $id);
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

            $ypeOfCare = new TypeOfCare();
            $ypeOfCare->setTitle($params['title']);
            $ypeOfCare->setSpace($space);

            $this->validate($ypeOfCare, null, ['api_lead_type_of_care_add']);

            $this->em->persist($ypeOfCare);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $ypeOfCare->getId();
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

            /** @var TypeOfCareRepository $repo */
            $repo = $this->em->getRepository(TypeOfCare::class);

            /** @var TypeOfCare $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(TypeOfCare::class), $id);

            if ($entity === null) {
                throw new TypeOfCareNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_lead_type_of_care_edit']);

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

            /** @var TypeOfCareRepository $repo */
            $repo = $this->em->getRepository(TypeOfCare::class);

            /** @var TypeOfCare $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(TypeOfCare::class), $id);

            if ($entity === null) {
                throw new TypeOfCareNotFoundException();
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
                throw new TypeOfCareNotFoundException();
            }

            /** @var TypeOfCareRepository $repo */
            $repo = $this->em->getRepository(TypeOfCare::class);

            $ypeOfCares = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(TypeOfCare::class), $ids);

            if (empty($ypeOfCares)) {
                throw new TypeOfCareNotFoundException();
            }

            /**
             * @var TypeOfCare $ypeOfCare
             */
            foreach ($ypeOfCares as $ypeOfCare) {
                $this->em->remove($ypeOfCare);
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
            throw new TypeOfCareNotFoundException();
        }

        /** @var TypeOfCareRepository $repo */
        $repo = $this->em->getRepository(TypeOfCare::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(TypeOfCare::class), $ids);

        if (empty($entities)) {
            throw new TypeOfCareNotFoundException();
        }

        return $this->getRelatedData(TypeOfCare::class, $entities);
    }
}
