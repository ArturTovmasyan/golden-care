<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\SalutationNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Salutation;
use App\Repository\SalutationRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class SalutationService
 * @package App\Api\V1\Admin\Service
 */
class SalutationService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return Paginator
     */
    public function getListing(QueryBuilder $queryBuilder, $params) : Paginator
    {
        /** @var SalutationRepository $salutationRepo */
        $salutationRepo = $this->em->getRepository(Salutation::class);

        return $salutationRepo->searchAll($queryBuilder);
    }

    /**
     * @param $id
     * @return Salutation|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Salutation::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $salutation = new Salutation();
            $salutation->setTitle($params['title']);

            $this->validate($salutation, null, ['api_admin_salutation_add']);

            $this->em->persist($salutation);
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

            /** @var Salutation $entity */
            $entity = $this->em->getRepository(Salutation::class)->find($id);

            if ($entity === null) {
                throw new SalutationNotFoundException();
            }

            $entity->setTitle($params['title']);

            $this->validate($entity, null, ['api_admin_salutation_edit']);

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

            /** @var Salutation $entity */
            $entity = $this->em->getRepository(Salutation::class)->find($id);

            if ($entity === null) {
                throw new SalutationNotFoundException();
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

        if (\count($ids)) {
            foreach ($ids as $id) {
                $this->remove($id);
            }
        }
    }
}