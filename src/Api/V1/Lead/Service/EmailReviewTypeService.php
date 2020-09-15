<?php

namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\EmailReviewTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\EmailReviewType;
use App\Entity\Space;
use App\Repository\Lead\EmailReviewTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class EmailReviewTypeService
 * @package App\Api\V1\Admin\Service
 */
class EmailReviewTypeService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var EmailReviewTypeRepository $repo */
        $repo = $this->em->getRepository(EmailReviewType::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EmailReviewType::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var EmailReviewTypeRepository $repo */
        $repo = $this->em->getRepository(EmailReviewType::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EmailReviewType::class));
    }

    /**
     * @param $id
     * @return EmailReviewType|null|object
     */
    public function getById($id)
    {
        /** @var EmailReviewTypeRepository $repo */
        $repo = $this->em->getRepository(EmailReviewType::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EmailReviewType::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $emailReviewType = new EmailReviewType();
            $emailReviewType->setTitle($params['title']);
            $emailReviewType->setSpace($space);

            $this->validate($emailReviewType, null, ['api_lead_email_review_type_add']);

            $this->em->persist($emailReviewType);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $emailReviewType->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Throwable
     */
    public function edit($id, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            /** @var EmailReviewTypeRepository $repo */
            $repo = $this->em->getRepository(EmailReviewType::class);

            /** @var EmailReviewType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EmailReviewType::class), $id);

            if ($entity === null) {
                throw new EmailReviewTypeNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_lead_email_review_type_edit']);

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

            /** @var EmailReviewTypeRepository $repo */
            $repo = $this->em->getRepository(EmailReviewType::class);

            /** @var EmailReviewType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EmailReviewType::class), $id);

            if ($entity === null) {
                throw new EmailReviewTypeNotFoundException();
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
                throw new EmailReviewTypeNotFoundException();
            }

            /** @var EmailReviewTypeRepository $repo */
            $repo = $this->em->getRepository(EmailReviewType::class);

            $emailReviewTypes = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EmailReviewType::class), $ids);

            if (empty($emailReviewTypes)) {
                throw new EmailReviewTypeNotFoundException();
            }

            /**
             * @var EmailReviewType $emailReviewType
             */
            foreach ($emailReviewTypes as $emailReviewType) {
                $this->em->remove($emailReviewType);
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
            throw new EmailReviewTypeNotFoundException();
        }

        /** @var EmailReviewTypeRepository $repo */
        $repo = $this->em->getRepository(EmailReviewType::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EmailReviewType::class), $ids);

        if (empty($entities)) {
            throw new EmailReviewTypeNotFoundException();
        }

        return $this->getRelatedData(EmailReviewType::class, $entities);
    }
}
