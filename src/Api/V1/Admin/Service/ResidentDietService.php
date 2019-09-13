<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DietNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentDietNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Diet;
use App\Entity\Resident;
use App\Entity\ResidentDiet;
use App\Repository\DietRepository;
use App\Repository\ResidentDietRepository;
use App\Repository\ResidentRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentDietService
 * @package App\Api\V1\Admin\Service
 */
class ResidentDietService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rd.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentDietRepository $repo */
        $repo = $this->em->getRepository(ResidentDiet::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiet::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentDietRepository $repo */
            $repo = $this->em->getRepository(ResidentDiet::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiet::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentDiet|null|object
     */
    public function getById($id)
    {
        /** @var ResidentDietRepository $repo */
        $repo = $this->em->getRepository(ResidentDiet::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiet::class), $id);
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

            $currentSpace = $this->grantService->getCurrentSpace();

            $residentId = $params['resident_id'] ?? 0;
            $dietId = $params['diet_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            /** @var DietRepository $dietRepo */
            $dietRepo = $this->em->getRepository(Diet::class);

            /** @var Diet $diet */
            $diet = $dietRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Diet::class), $dietId);

            if ($diet === null) {
                throw new DietNotFoundException();
            }

            $residentDiet = new ResidentDiet();
            $residentDiet->setResident($resident);
            $residentDiet->setDiet($diet);
            $residentDiet->setDescription($params['description']);

            $this->validate($residentDiet, null, ['api_admin_resident_diet_add']);

            $this->em->persist($residentDiet);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentDiet->getId();
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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentDietRepository $repo */
            $repo = $this->em->getRepository(ResidentDiet::class);

            /** @var ResidentDiet $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiet::class), $id);

            if ($entity === null) {
                throw new ResidentDietNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;
            $dietId = $params['diet_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            /** @var DietRepository $dietRepo */
            $dietRepo = $this->em->getRepository(Diet::class);

            /** @var Diet $diet */
            $diet = $dietRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Diet::class), $dietId);

            if ($diet === null) {
                throw new DietNotFoundException();
            }

            $entity->setResident($resident);
            $entity->setDiet($diet);
            $entity->setDescription($params['description']);

            $this->validate($entity, null, ['api_admin_resident_diet_edit']);

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

            /** @var ResidentDietRepository $repo */
            $repo = $this->em->getRepository(ResidentDiet::class);

            /** @var ResidentDiet $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiet::class), $id);

            if ($entity === null) {
                throw new ResidentDietNotFoundException();
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
                throw new ResidentDietNotFoundException();
            }

            /** @var ResidentDietRepository $repo */
            $repo = $this->em->getRepository(ResidentDiet::class);

            $residentDiets = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiet::class), $ids);

            if (empty($residentDiets)) {
                throw new ResidentDietNotFoundException();
            }

            /**
             * @var ResidentDiet $residentDiet
             */
            foreach ($residentDiets as $residentDiet) {
                $this->em->remove($residentDiet);
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
            throw new ResidentDietNotFoundException();
        }

        /** @var ResidentDietRepository $repo */
        $repo = $this->em->getRepository(ResidentDiet::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiet::class), $ids);

        if (empty($entities)) {
            throw new ResidentDietNotFoundException();
        }

        return $this->getRelatedData(ResidentDiet::class, $entities);
    }
}
