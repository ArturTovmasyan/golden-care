<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\PaymentSourceNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\PaymentSource;
use App\Entity\Space;
use App\Repository\PaymentSourceRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PaymentSourceService
 * @package App\Api\V1\Admin\Service
 */
class PaymentSourceService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var PaymentSourceRepository $repo */
        $repo = $this->em->getRepository(PaymentSource::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var PaymentSourceRepository $repo */
        $repo = $this->em->getRepository(PaymentSource::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSource::class));
    }

    /**
     * @param $id
     * @return PaymentSource|null|object
     */
    public function getById($id)
    {
        /** @var PaymentSourceRepository $repo */
        $repo = $this->em->getRepository(PaymentSource::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $id);
    }

    /**
     * @param array $params
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

            $paymentSource = new PaymentSource();
            $paymentSource->setTitle($params['title']);
            $paymentSource->setSpace($space);

            $this->validate($paymentSource, null, ['api_admin_payment_source_add']);

            $this->em->persist($paymentSource);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $paymentSource->getId();
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

            /** @var PaymentSourceRepository $repo */
            $repo = $this->em->getRepository(PaymentSource::class);

            /** @var PaymentSource $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $id);

            if ($entity === null) {
                throw new PaymentSourceNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_payment_source_edit']);

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

            /** @var PaymentSourceRepository $repo */
            $repo = $this->em->getRepository(PaymentSource::class);

            /** @var PaymentSource $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $id);

            if ($entity === null) {
                throw new PaymentSourceNotFoundException();
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
                throw new PaymentSourceNotFoundException();
            }

            /** @var PaymentSourceRepository $repo */
            $repo = $this->em->getRepository(PaymentSource::class);

            $paymentSources = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $ids);

            if (empty($paymentSources)) {
                throw new PaymentSourceNotFoundException();
            }

            /**
             * @var PaymentSource $paymentSource
             */
            foreach ($paymentSources as $paymentSource) {
                $this->em->remove($paymentSource);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
