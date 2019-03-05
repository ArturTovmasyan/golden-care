<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\MedicationNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Medication;
use App\Entity\Space;
use App\Repository\MedicationRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class MedicationService
 * @package App\Api\V1\Admin\Service
 */
class MedicationService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var MedicationRepository $repo */
        $repo = $this->em->getRepository(Medication::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Medication::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var MedicationRepository $repo */
        $repo = $this->em->getRepository(Medication::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Medication::class));
    }

    /**
     * @param $id
     * @return Medication|null|object
     */
    public function getById($id)
    {
        /** @var MedicationRepository $repo */
        $repo = $this->em->getRepository(Medication::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Medication::class), $id);
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

            // save Medication
            $medication = new Medication();
            $medication->setTitle($params['title'] ?? null);
            $medication->setSpace($space);

            $this->validate($medication, null, ['api_admin_medication_add']);

            $this->em->persist($medication);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $medication->getId();
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
    public function edit($id, array $params): void
    {
        try {
            /**
             * @var Medication $medication
             */
            $this->em->getConnection()->beginTransaction();

            /** @var MedicationRepository $repo */
            $repo = $this->em->getRepository(Medication::class);

            $medication = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Medication::class), $id);

            if ($medication === null) {
                throw new MedicationNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $medication->setTitle($params['title'] ?? null);
            $medication->setSpace($space);

            $this->validate($medication, null, ['api_admin_medication_edit']);

            $this->em->persist($medication);
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
    public function remove($id): void
    {
        try {
            /**
             * @var Medication $medication
             */
            $this->em->getConnection()->beginTransaction();

            /** @var MedicationRepository $repo */
            $repo = $this->em->getRepository(Medication::class);

            $medication = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Medication::class), $id);

            if ($medication === null) {
                throw new MedicationNotFoundException();
            }

            $this->em->remove($medication);
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
                throw new MedicationNotFoundException();
            }

            /** @var MedicationRepository $repo */
            $repo = $this->em->getRepository(Medication::class);

            $medications = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Medication::class), $ids);

            if (empty($medications)) {
                throw new MedicationNotFoundException();
            }

            /**
             * @var Medication $medication
             */
            foreach ($medications as $medication) {
                $this->em->remove($medication);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
