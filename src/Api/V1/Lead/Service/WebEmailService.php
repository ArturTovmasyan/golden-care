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
use App\Repository\SpaceRepository;
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
     * @throws \Exception
     */
    public function add(array $params)
    {
        $spam = true;
        if (empty($params['Spam'])) {
            $spam = (bool)$params['Spam'];
        }

        if (!$spam) {
            try {
                $this->em->getConnection()->beginTransaction();

                /** @var SpaceRepository $spaceRepo */
                $spaceRepo = $this->em->getRepository(Space::class);

                /** @var Space $space */
                $space = $spaceRepo->getLast();

                if ($space === null) {
                    throw new SpaceNotFoundException();
                }

                $subject = null;
                if (!empty($params['Subject']) && (stripos($params['Subject'], 'new submission') !== false || stripos($params['Subject'], 'new form entry') !== fals)) {
                    $subject = $params['Subject'];
                }

                if ($subject === null) {
                    throw new SubjectNotBeBlankException();
                }

                $now = new \DateTime('now');
                $now->setTime(0, 0, 0);

                $webEmail = new WebEmail();
                $webEmail->setSpace($space);
                $webEmail->setEmailReviewType(null);
                $webEmail->setDate($now);
                $webEmail->setSubject($subject);
                $webEmail->setUpdatedBy($webEmail->getCreatedBy());
                $webEmail->setEmailed(false);

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

                        if ($facility === null) {
                            /** @var Facility $value */
                            foreach ($facilities as $value) {
                                if (stripos($params['Subject'], $value->getName()) !== false) {
                                    $facility = $value;
                                    break;
                                }
                            }
                        }
                    }

                    $webEmail->setFacility($facility);
                } else {
                    $webEmail->setFacility(null);
                }

                if (!empty($params['Name'])) {
                    $webEmail->setName($params['Name']);
                } else {
                    $webEmail->setName(null);
                }

                if (!empty($params['Email'])) {
                    $webEmail->setEmail($params['Email']);
                } else {
                    $webEmail->setEmail(null);
                }

                if (!empty($params['Phone'])) {
                    if (!empty($params['Message']) && stripos($params['Message'], $params['Phone']) !== false) {
                        $phone = null;
                    } else {
                        $phone = $this->formatPhoneUs($params['Phone']);
                    }

                    $webEmail->setPhone($phone);
                } else {
                    $webEmail->setPhone(null);
                }

                $message = !empty($params['Message']) ? mb_strimwidth($params['Message'], 0, 2048) : '';
                $webEmail->setMessage($message);

                $this->validate($webEmail, null, ['api_lead_web_email_add']);

                $this->em->persist($webEmail);
                $this->em->flush();
                $this->em->getConnection()->commit();
            } catch (\Exception $e) {
                $this->em->getConnection()->rollBack();

                throw $e;
            }
        }
    }

    private function formatPhoneUs($phone) {
        //strip out everything but numbers
        $phone = preg_replace('/\D/', '', $phone);
        $length = strlen($phone);

        switch($length) {
            case 7:
                return preg_replace('/(\d{3})(\d{4})/', '(000) $1-$2', $phone);
                break;
            case 10:
                return preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $phone);
                break;
            case 11:
                return preg_replace('/(\d{1})(\d{3})(\d{3})(\d{4})/', '($2) $3-$4', $phone);
                break;
            default:
                return null;
                break;
        }
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
                $date = new \DateTime($params['date']);
                $date->setTime(0, 0, 0);

                $entity->setDate($date);
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
            $entity->setName($params['name']);
            $entity->setEmail($params['email']);
            $entity->setPhone($params['phone']);
            $entity->setMessage($params['message']);

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
