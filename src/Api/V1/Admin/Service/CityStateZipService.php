<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CityStateZip;
use App\Entity\Space;
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
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(CityStateZip::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(CityStateZip::class)->findAll();
    }

    /**
     * @param $id
     * @return CityStateZip|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(CityStateZip::class)->find($id);
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

            /** @var CityStateZip $entity */
            $entity = $this->em->getRepository(CityStateZip::class)->find($id);

            if ($entity === null) {
                throw new CityStateZipNotFoundException();
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

            /** @var CityStateZip $entity */
            $entity = $this->em->getRepository(CityStateZip::class)->find($id);

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
            if (empty($ids)) {
                throw new CityStateZipNotFoundException();
            }

            $cszs = $this->em->getRepository(CityStateZip::class)->findByIds($ids);

            if (empty($cszs)) {
                throw new CityStateZipNotFoundException();
            }

            $this->em->getConnection()->beginTransaction();

            /**
             * @var CityStateZip $csz
             */
            foreach ($cszs as $csz) {
                $this->em->remove($csz);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (CityStateZipNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
