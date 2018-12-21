<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AllergenNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Allergen;
use App\Entity\Space;
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

            $spaceId = $params['space_id'] ?? 0;

            $space = null;

            if ($spaceId && $spaceId > 0) {
                /** @var Space $space */
                $space = $this->em->getRepository(Space::class)->find($spaceId);


                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            $allergen = new Allergen();
            $allergen->setTitle($params['title']);
            $allergen->setDescription($params['description']);
            $allergen->setSpace($space);

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
            $entity->setDescription($params['description']);
            $entity->setSpace($space);

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
     * @param array $ids
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids)
    {
        try {
            if (empty($ids)) {
                throw new AllergenNotFoundException();
            }

            $allergens = $this->em->getRepository(Allergen::class)->findByIds($ids);

            if (empty($allergens)) {
                throw new AllergenNotFoundException();
            }

            /**
             * @var Allergen $allergen
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($allergens as $allergen) {
                $this->em->remove($allergen);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch(AllergenNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
