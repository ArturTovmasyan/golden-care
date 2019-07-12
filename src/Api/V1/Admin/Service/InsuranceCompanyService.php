<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\InsuranceCompanyNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\InsuranceCompany;
use App\Entity\Space;
use App\Repository\InsuranceCompanyRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class InsuranceCompanyService
 * @package App\Api\V1\Admin\Service
 */
class InsuranceCompanyService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var InsuranceCompanyRepository $repo */
        $repo = $this->em->getRepository(InsuranceCompany::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(InsuranceCompany::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var InsuranceCompanyRepository $repo */
        $repo = $this->em->getRepository(InsuranceCompany::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(InsuranceCompany::class));
    }

    /**
     * @param $id
     * @return InsuranceCompany|null|object
     */
    public function getById($id)
    {
        /** @var InsuranceCompanyRepository $repo */
        $repo = $this->em->getRepository(InsuranceCompany::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(InsuranceCompany::class), $id);
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

            $careLevel = new InsuranceCompany();
            $careLevel->setTitle($params['title']);
            $careLevel->setSpace($space);

            $this->validate($careLevel, null, ['api_admin_insurance_company_add']);

            $this->em->persist($careLevel);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $careLevel->getId();
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

            /** @var InsuranceCompanyRepository $repo */
            $repo = $this->em->getRepository(InsuranceCompany::class);

            /** @var InsuranceCompany $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(InsuranceCompany::class), $id);

            if ($entity === null) {
                throw new InsuranceCompanyNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_insurance_company_edit']);

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

            /** @var InsuranceCompanyRepository $repo */
            $repo = $this->em->getRepository(InsuranceCompany::class);

            /** @var InsuranceCompany $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(InsuranceCompany::class), $id);

            if ($entity === null) {
                throw new InsuranceCompanyNotFoundException();
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
                throw new InsuranceCompanyNotFoundException();
            }

            /** @var InsuranceCompanyRepository $repo */
            $repo = $this->em->getRepository(InsuranceCompany::class);

            $careLevels = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(InsuranceCompany::class), $ids);

            if (empty($careLevels)) {
                throw new InsuranceCompanyNotFoundException();
            }

            /**
             * @var InsuranceCompany $careLevel
             */
            foreach ($careLevels as $careLevel) {
                $this->em->remove($careLevel);
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
            throw new InsuranceCompanyNotFoundException();
        }

        /** @var InsuranceCompanyRepository $repo */
        $repo = $this->em->getRepository(InsuranceCompany::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(InsuranceCompany::class), $ids);

        if (empty($entities)) {
            throw new InsuranceCompanyNotFoundException();
        }

        return $this->getRelatedData(InsuranceCompany::class, $entities);
    }
}
