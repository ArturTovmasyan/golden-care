<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\MedicationFormFactorNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\MedicationFormFactor;
use App\Entity\Space;
use App\Repository\MedicationFormFactorRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class MedicationFormFactorService
 * @package App\Api\V1\Admin\Service
 */
class MedicationFormFactorService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var MedicationFormFactorRepository $repo */
        $repo = $this->em->getRepository(MedicationFormFactor::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(MedicationFormFactor::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var MedicationFormFactorRepository $repo */
        $repo = $this->em->getRepository(MedicationFormFactor::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(MedicationFormFactor::class));
    }

    /**
     * @param $id
     * @return MedicationFormFactor|null|object
     */
    public function getById($id)
    {
        /** @var MedicationFormFactorRepository $repo */
        $repo = $this->em->getRepository(MedicationFormFactor::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(MedicationFormFactor::class), $id);
    }

    /**
     * @param array $params
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

            $medicationFormFactor = new MedicationFormFactor();
            $medicationFormFactor->setTitle($params['title']);
            $medicationFormFactor->setSpace($space);

            $this->validate($medicationFormFactor, null, ['api_admin_medication_form_factor_add']);

            $this->em->persist($medicationFormFactor);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $medicationFormFactor->getId();
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

            /** @var MedicationFormFactorRepository $repo */
            $repo = $this->em->getRepository(MedicationFormFactor::class);

            /** @var MedicationFormFactor $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(MedicationFormFactor::class), $id);

            if ($entity === null) {
                throw new MedicationFormFactorNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_medication_form_factor_edit']);

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

            /** @var MedicationFormFactorRepository $repo */
            $repo = $this->em->getRepository(MedicationFormFactor::class);

            /** @var MedicationFormFactor $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(MedicationFormFactor::class), $id);

            if ($entity === null) {
                throw new MedicationFormFactorNotFoundException();
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
                throw new MedicationFormFactorNotFoundException();
            }

            /** @var MedicationFormFactorRepository $repo */
            $repo = $this->em->getRepository(MedicationFormFactor::class);

            $factors = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(MedicationFormFactor::class), $ids);

            if (empty($factors)) {
                throw new MedicationFormFactorNotFoundException();
            }

            /**
             * @var MedicationFormFactorNotFoundException $factor
             */
            foreach ($factors as $factor) {
                $this->em->remove($factor);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
