<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\MedicationNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Medication;
use App\Entity\Space;
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
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Medication::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Medication::class)->findAll();
    }

    /**
     * @param $id
     * @return Medication|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Medication::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function add(array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $spaceId = $params['space_id'] ?? 0;

            $space = null;

            if ($spaceId && $spaceId > 0) {
                /** @var Space $space */
                $space = $this->em->getRepository(Space::class)->find($spaceId);


                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            // save Medication
            $medication = new Medication();
            $medication->setTitle($params['title'] ?? null);
            $medication->setSpace($space);

            $this->validate($medication, null, ["api_admin_medication_add"]);

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
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function edit($id, array $params): void
    {
        try {
            /**
             * @var Medication $medication
             */
            $this->em->getConnection()->beginTransaction();

            $medication = $this->em->getRepository(Medication::class)->find($id);

            if (is_null($medication)) {
                throw new MedicationNotFoundException();
            }

            $spaceId = $params['space_id'] ?? 0;

            $space = null;

            if ($spaceId && $spaceId > 0) {
                /** @var Space $space */
                $space = $this->em->getRepository(Space::class)->find($spaceId);


                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            $medication->setTitle($params['title'] ?? null);
            $medication->setSpace($space);

            $this->validate($medication, null, ["api_admin_medication_edit"]);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id): void
    {
        try {
            /**
             * @var Medication $medication
             */
            $this->em->getConnection()->beginTransaction();

            $medication = $this->em->getRepository(Medication::class)->find($id);

            if (is_null($medication)) {
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            if (empty($ids)) {
                throw new MedicationNotFoundException();
            }

            $medications = $this->em->getRepository(Medication::class)->findByIds($ids);

            if (empty($medications)) {
                throw new MedicationNotFoundException();
            }

            /**
             * @var Medication $medication
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($medications as $medication) {
                $this->em->remove($medication);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (MedicationNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
