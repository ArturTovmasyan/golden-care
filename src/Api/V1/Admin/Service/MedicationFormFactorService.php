<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\MedicationFormFactorNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\MedicationFormFactor;
use App\Entity\Space;
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
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(MedicationFormFactor::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(MedicationFormFactor::class)->findAll();
    }

    /**
     * @param $id
     * @return MedicationFormFactor|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(MedicationFormFactor::class)->find($id);
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

            $space = null;

            if ($spaceId && $spaceId > 0) {
                /** @var Space $space */
                $space = $this->em->getRepository(Space::class)->find($spaceId);


                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            $medicationFormFactor = new MedicationFormFactor();
            $medicationFormFactor->setTitle($params['title']);
            $medicationFormFactor->setSpace($space);

            $this->validate($medicationFormFactor, null, ['api_admin_medication_form_factor_add']);

            $this->em->persist($medicationFormFactor);
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

            /** @var MedicationFormFactor $entity */
            $entity = $this->em->getRepository(MedicationFormFactor::class)->find($id);

            if ($entity === null) {
                throw new MedicationFormFactorNotFoundException();
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var MedicationFormFactor $entity */
            $entity = $this->em->getRepository(MedicationFormFactor::class)->find($id);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            if (empty($ids)) {
                throw new MedicationFormFactorNotFoundException();
            }

            $factors = $this->em->getRepository(MedicationFormFactor::class)->findByIds($ids);

            if (empty($factors)) {
                throw new MedicationFormFactorNotFoundException();
            }

            /**
             * @var MedicationFormFactorNotFoundException $factor
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($factors as $factor) {
                $this->em->remove($factor);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (MedicationFormFactorNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
