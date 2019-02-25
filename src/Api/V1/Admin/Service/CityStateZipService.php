<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CityStateZip;
use App\Entity\Space;
use App\Repository\CityStateZipRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CityStateZipService
 * @package App\Api\V1\Admin\Service
 */
class CityStateZipService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var CityStateZipRepository $repo */
        $repo = $this->em->getRepository(CityStateZip::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $queryBuilder);
    }

    public function list($params)
    {
        /** @var CityStateZipRepository $repo */
        $repo = $this->em->getRepository(CityStateZip::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CityStateZip::class));
    }

    /**
     * @param $id
     * @return CityStateZip|null|object
     */
    public function getById($id)
    {
        /** @var CityStateZipRepository $repo */
        $repo = $this->em->getRepository(CityStateZip::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $id);
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

            $cityStateZip = new CityStateZip();
            $cityStateZip->setStateFull($params['state_full']);
            $cityStateZip->setStateAbbr($params['state_abbr']);
            $cityStateZip->setZipMain($params['zip_main']);
            $cityStateZip->setZipSub($params['zip_sub']);
            $cityStateZip->setCity($params['city']);
            $cityStateZip->setSpace($space);

            $this->validate($cityStateZip, null, ['api_admin_city_state_zip_add']);

            $this->em->persist($cityStateZip);
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

            /** @var CityStateZipRepository $repo */
            $repo = $this->em->getRepository(CityStateZip::class);

            /** @var CityStateZip $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $id);

            if ($entity === null) {
                throw new CityStateZipNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setStateFull($params['state_full']);
            $entity->setStateAbbr($params['state_abbr']);
            $entity->setZipMain($params['zip_main']);
            $entity->setZipSub($params['zip_sub']);
            $entity->setCity($params['city']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_city_state_zip_edit']);

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

            /** @var CityStateZipRepository $repo */
            $repo = $this->em->getRepository(CityStateZip::class);

            /** @var CityStateZip $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $id);

            if ($entity === null) {
                throw new CityStateZipNotFoundException();
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
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new CityStateZipNotFoundException();
            }

            /** @var CityStateZipRepository $repo */
            $repo = $this->em->getRepository(CityStateZip::class);

            $cszs = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $ids);

            if (empty($cszs)) {
                throw new CityStateZipNotFoundException();
            }

            /**
             * @var CityStateZip $csz
             */
            foreach ($cszs as $csz) {
                $this->em->remove($csz);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
