<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\SpecialityNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Speciality;
use App\Entity\Space;
use App\Repository\SpecialityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SpecialityService
 * @package App\Api\V1\Admin\Service
 */
class SpecialityService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var SpecialityRepository $repo */
        $repo = $this->em->getRepository(Speciality::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Speciality::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var SpecialityRepository $repo */
        $repo = $this->em->getRepository(Speciality::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Speciality::class));
    }

    /**
     * @param $id
     * @return Speciality|null|object
     */
    public function getById($id)
    {
        /** @var SpecialityRepository $repo */
        $repo = $this->em->getRepository(Speciality::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Speciality::class), $id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            /**
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            $spaceId = $params['space_id'] ?? 0;

            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $speciality = new Speciality();
            $speciality->setTitle($params['title']);
            $speciality->setSpace($space);

            $this->validate($speciality, null, ['api_admin_speciality_add']);

            $this->em->persist($speciality);
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
            /**
             * @var Speciality $entity
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            /** @var SpecialityRepository $repo */
            $repo = $this->em->getRepository(Speciality::class);

            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Speciality::class), $id);

            if ($entity === null) {
                throw new SpecialityNotFoundException();
            }

            $spaceId = $params['space_id'] ?? 0;

            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_speciality_edit']);

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

            /** @var SpecialityRepository $repo */
            $repo = $this->em->getRepository(Speciality::class);

            /** @var Speciality $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Speciality::class), $id);

            if ($entity === null) {
                throw new SpecialityNotFoundException();
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
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new SpecialityNotFoundException();
            }

            /** @var SpecialityRepository $repo */
            $repo = $this->em->getRepository(Speciality::class);

            $specialities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Speciality::class), $ids);

            if (empty($specialities)) {
                throw new SpecialityNotFoundException();
            }

            /**
             * @var Speciality $speciality
             */
            foreach ($specialities as $speciality) {
                $this->em->remove($speciality);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
