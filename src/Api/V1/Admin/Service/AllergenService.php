<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AllergenNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Allergen;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AllergenService
 * @package App\Api\V1\Admin\Service
 */
class AllergenService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Allergen::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Allergen::class)->findAll();
    }

    /**
     * @param $id
     * @return Allergen|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Allergen::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $allergen = new Allergen();
            $allergen->setTitle($params['title']);
            $allergen->setDescription($params['description']);

            $this->validate($allergen, null, ['api_admin_allergen_add']);

            $this->em->persist($allergen);
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

            /** @var Allergen $entity */
            $entity = $this->em->getRepository(Allergen::class)->find($id);

            if ($entity === null) {
                throw new AllergenNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setDescription($params['description']);

            $this->validate($entity, null, ['api_admin_allergen_edit']);

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

            /** @var Allergen $entity */
            $entity = $this->em->getRepository(Allergen::class)->find($id);

            if ($entity === null) {
                throw new AllergenNotFoundException();
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
