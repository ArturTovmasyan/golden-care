<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DiagnosisNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Diagnosis;
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
        $this->em->getRepository(Diagnosis::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Diagnosis::class)->findAll();
    }

    /**
     * @param $id
     * @return Diagnosis|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Diagnosis::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $diagnosis = new Diagnosis();
            $diagnosis->setTitle($params['title']);
            $diagnosis->setAcronym($params['acronym']);
            $diagnosis->setDescription($params['description']);

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
            $entity = $this->em->getRepository(Diagnosis::class)->find($id);

            if ($entity === null) {
                throw new DiagnosisNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setAcronym($params['acronym']);
            $entity->setDescription($params['description']);

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
            $entity = $this->em->getRepository(Diagnosis::class)->find($id);

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
     * @param array $params
     */
    public function removeBulk(array $params)
    {
        $ids = $params['ids'];

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $this->remove($id);
            }
        }
    }
}
