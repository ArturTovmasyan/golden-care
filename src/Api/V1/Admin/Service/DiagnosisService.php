<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DiagnosisNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Diagnosis;
use App\Entity\Space;
use App\Repository\DiagnosisRepository;
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
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var DiagnosisRepository $repo */
        $repo = $this->em->getRepository(Diagnosis::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Diagnosis::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var DiagnosisRepository $repo */
        $repo = $this->em->getRepository(Diagnosis::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Diagnosis::class));
    }

    /**
     * @param $id
     * @return Diagnosis|null|object
     */
    public function getById($id)
    {
        /** @var DiagnosisRepository $repo */
        $repo = $this->em->getRepository(Diagnosis::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Diagnosis::class), $id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

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

            /** @var DiagnosisRepository $repo */
            $repo = $this->em->getRepository(Diagnosis::class);

            /** @var Diagnosis $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Diagnosis::class), $id);

            if ($entity === null) {
                throw new DiagnosisNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

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
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var DiagnosisRepository $repo */
            $repo = $this->em->getRepository(Diagnosis::class);

            /** @var Diagnosis $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Diagnosis::class), $id);

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
     * @throws \Throwable
     */
    public function removeBulk(array $ids)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new DiagnosisNotFoundException();
            }

            /** @var DiagnosisRepository $repo */
            $repo = $this->em->getRepository(Diagnosis::class);

            $diagnoses = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Diagnosis::class), $ids);

            if (empty($diagnoses)) {
                throw new DiagnosisNotFoundException();
            }

            /**
             * @var Diagnosis $diagnosis
             */
            foreach ($diagnoses as $diagnosis) {
                $this->em->remove($diagnosis);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
