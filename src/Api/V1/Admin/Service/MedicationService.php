<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\MedicationNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Medication;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class MedicationService
 * @package App\Api\V1\Admin\Service
 */
class MedicationService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return Paginator
     */
    public function getListing(QueryBuilder $queryBuilder, $params)
    {
        return $this->em->getRepository(Medication::class)->search($queryBuilder);
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

            // save Medication
            $medication = new Medication();
            $medication->setName($params['name'] ?? null);
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

            $medication->setName($params['name'] ?? null);
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
}