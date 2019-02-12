<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DiagnosisNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Diagnosis;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DiagnosisService
 * @package App\Api\V1\Admin\Service
 */
class DiagnosisService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Diagnosis::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Diagnosis::class)->list($this->grantService->getCurrentSpace());
    }

    /**
     * @param $id
     * @return Diagnosis|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Diagnosis::class)->getOne($this->grantService->getCurrentSpace(), $id);
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

            $diagnosis = new Diagnosis();
            $diagnosis->setTitle($params['title']);
            $diagnosis->setAcronym($params['acronym']);
            $diagnosis->setDescription($params['description']);
            $diagnosis->setSpace($space);

            $this->validate($diagnosis, null, ['api_admin_diagnosis_add']);

            $this->em->persist($diagnosis);
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

            /** @var Diagnosis $entity */
            $entity = $this->em->getRepository(Diagnosis::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($entity === null) {
                throw new DiagnosisNotFoundException();
            }

            $spaceId = $params['space_id'] ?? 0;

            /** @var Space $space */
            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setAcronym($params['acronym']);
            $entity->setDescription($params['description']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_diagnosis_edit']);

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

            /** @var Diagnosis $entity */
            $entity = $this->em->getRepository(Diagnosis::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($entity === null) {
                throw new DiagnosisNotFoundException();
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
    public function removeBulk(array $ids)
    {
        try {
            if (empty($ids)) {
                throw new DiagnosisNotFoundException();
            }

            $diagnoses = $this->em->getRepository(Diagnosis::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

            if (empty($diagnoses)) {
                throw new DiagnosisNotFoundException();
            }

            /**
             * @var Diagnosis $diagnosis
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($diagnoses as $diagnosis) {
                $this->em->remove($diagnosis);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (DiagnosisNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
