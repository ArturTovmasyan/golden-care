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
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rd.resident = :residentId')
            ->setParameter('residentId', $residentId);

        $this->em->getRepository(ResidentDiet::class)->search($queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(ResidentDiet::class)->findBy(['resident' => $residentId]);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentDiet|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResidentDiet::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $residentId = $params['resident_id'] ?? 0;
            $dietId = $params['diet_id'] ?? 0;

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->find($residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            /** @var Diet $diet */
            $diet = $this->em->getRepository(Diet::class)->find($dietId);

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

            /** @var ResidentDiet $entity */
            $entity = $this->em->getRepository(ResidentDiet::class)->find($id);

            if ($entity === null) {
                throw new ResidentDietNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;
            $dietId = $params['diet_id'] ?? 0;

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->find($residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            /** @var Diet $diet */
            $diet = $this->em->getRepository(Diet::class)->find($dietId);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResidentDiet $entity */
            $entity = $this->em->getRepository(ResidentDiet::class)->find($id);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            if (empty($ids)) {
                throw new ResidentDietNotFoundException();
            }

            $residentDiets = $this->em->getRepository(ResidentDiet::class)->findByIds($ids);

            if (empty($residentDiets)) {
                throw new ResidentDietNotFoundException();
            }

            /**
             * @var ResidentDiet $residentDiet
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($residentDiets as $residentDiet) {
                $this->em->remove($residentDiet);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ResidentDietNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
