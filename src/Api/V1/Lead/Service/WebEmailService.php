<?php

namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\EmailReviewTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\WebEmailNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\SubjectNotBeBlankException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Facility;
use App\Entity\Lead\EmailReviewType;
use App\Entity\Lead\WebEmail;
use App\Entity\Space;
use App\Repository\FacilityRepository;
use App\Repository\Lead\EmailReviewTypeRepository;
use App\Repository\Lead\WebEmailRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class WebEmailService
 * @package App\Api\V1\Admin\Service
 */
class WebEmailService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var WebEmailRepository $repo */
        $repo = $this->em->getRepository(WebEmail::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(WebEmail::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var WebEmailRepository $repo */
        $repo = $this->em->getRepository(WebEmail::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(WebEmail::class));
    }

    /**
     * @param $id
     * @param $gridData
     * @return WebEmail|null|object
     */
    public function getById($id, $gridData)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var WebEmailRepository $repo */
        $repo = $this->em->getRepository(WebEmail::class);
        /** @var WebEmail $entity */
        $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(WebEmail::class), $id);

        $this->setPreviousAndNextItemIdsFromGrid($entity, $gridData);

        return $entity;
    }

    /**
     * @param array $params
     * @param $body
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params, $body): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $subject = null;
            if (!empty($params['Subject']) && stripos($params['Subject'], 'new submission') !== false) {
                $subject = $params['Subject'];
            }

            if ($subject === null) {
                throw new SubjectNotBeBlankException();
            }

            $now = new \DateTime('now');

            $webEmail = new WebEmail();
            $webEmail->setSpace($space);
            $webEmail->setEmailReviewType(null);
            $webEmail->setDate($now);
            $webEmail->setSubject($subject);
            $webEmail->setBody($body);

            $facility = null;
            if (!empty($params['From'])) {
                $from = explode(' <', $params['From']);
                $potentialName = $from[0];

                /** @var FacilityRepository $facilityRepo */
                $facilityRepo = $this->em->getRepository(Facility::class);

                $facilities = $facilityRepo->findBy(['space' => $space]);

                if (!empty($facilities)) {
                    /** @var Facility $value */
                    foreach ($facilities as $value) {
                        if (in_array($potentialName, $value->getPotentialNames(), false)) {
                            $facility = $value;
                            break;
                        }
                    }
                }

                $webEmail->setFacility($facility);
            } else {
                $webEmail->setFacility(null);
            }

            $this->validate($webEmail, null, ['api_lead_web_email_add']);

            $this->em->persist($webEmail);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $webEmail->getId();
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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var WebEmailRepository $repo */
            $repo = $this->em->getRepository(WebEmail::class);

            /** @var WebEmail $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(WebEmail::class), $id);

            if ($entity === null) {
                throw new WebEmailNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setSpace($space);

            $date = $params['date'];
            if (!empty($date)) {
                $entity->setDate(new \DateTime($params['date']));
            } else {
                $entity->setDate(null);
            }

            if (!empty($params['facility_id'])) {
                /** @var FacilityRepository $facilityRepo */
                $facilityRepo = $this->em->getRepository(Facility::class);

                /** @var Facility $facility */
                $facility = $facilityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $params['facility_id']);

                if ($facility === null) {
                    throw new FacilityNotFoundException();
                }

                $entity->setFacility($facility);
            } else {
                $entity->setFacility(null);
            }

            if (!empty($params['email_review_type_id'])) {
                /** @var EmailReviewTypeRepository $reviewTypeRepo */
                $reviewTypeRepo = $this->em->getRepository(EmailReviewType::class);

                /** @var EmailReviewType $reviewType */
                $reviewType = $reviewTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(EmailReviewType::class), $params['email_review_type_id']);

                if ($reviewType === null) {
                    throw new EmailReviewTypeNotFoundException();
                }

                $entity->setEmailReviewType($reviewType);
            } else {
                $entity->setEmailReviewType(null);
            }

            $entity->setSubject($params['subject']);
            $entity->setBody($params['body']);

            $this->validate($entity, null, ['api_lead_web_email_edit']);

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

            /** @var WebEmailRepository $repo */
            $repo = $this->em->getRepository(WebEmail::class);

            /** @var WebEmail $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(WebEmail::class), $id);

            if ($entity === null) {
                throw new WebEmailNotFoundException();
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
                throw new WebEmailNotFoundException();
            }

            /** @var WebEmailRepository $repo */
            $repo = $this->em->getRepository(WebEmail::class);

            $webEmails = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(WebEmail::class), $ids);

            if (empty($webEmails)) {
                throw new WebEmailNotFoundException();
            }

            /**
             * @var WebEmail $webEmail
             */
            foreach ($webEmails as $webEmail) {
                $this->em->remove($webEmail);
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
            throw new WebEmailNotFoundException();
        }

        /** @var WebEmailRepository $repo */
        $repo = $this->em->getRepository(WebEmail::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(WebEmail::class), $ids);

        if (empty($entities)) {
            throw new WebEmailNotFoundException();
        }

        return $this->getRelatedData(WebEmail::class, $entities);
    }
}
