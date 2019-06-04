<?php
namespace App\Api\V1\Common\Service;

use App\Annotation\ValidationSerializedName;
use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Api\V1\Component\RelatedInfoInterface;
use App\Entity\Role;
use App\Entity\Space;
use App\Model\Grant;
use App\Util\Mailer;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class BaseService
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $encoder;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var Security
     */
    protected $security;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var GrantService
     */
    protected $grantService;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * BaseService constructor.
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @param Mailer $mailer
     * @param ValidatorInterface $validator
     * @param Security $security
     * @param Reader $reader
     * @param GrantService $grantService
     * @param ContainerInterface $container
     */
    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder,
        Mailer $mailer,
        ValidatorInterface $validator,
        Security $security,
        Reader $reader,
        GrantService $grantService,
        ContainerInterface $container
    ) {
        $this->em           = $em;
        $this->encoder      = $encoder;
        $this->mailer       = $mailer;
        $this->validator    = $validator;
        $this->security     = $security;
        $this->reader       = $reader;
        $this->grantService = $grantService;
        $this->container    = $container;
    }

    /**
     * @param $entity
     * @param null $constraints
     * @param null $groups
     * @return bool
     * @throws \ReflectionException
     */
    protected function validate($entity, $constraints = null, $groups = null)
    {
        $validationErrors = $this->validator->validate($entity, $constraints, $groups);
        $errors           = [];

        if ($validationErrors->count() > 0) {
            foreach ($validationErrors as $error) {
                $propertyPath = ValidationSerializedName::convert(
                    $this->reader,
                    $this->em->getClassMetadata(\get_class($entity))->getName(),
                    $groups[0],
                    $error->getPropertyPath()
                );

                $errors[$propertyPath] = $error->getMessage();
            }

            throw new ValidationException($errors);
        }

        return true;
    }

    /**
     * @param int $length
     * @return bool|string
     */
    protected function generatePassword($length = 8)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";

        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * @param $spaceId
     * @return Space|null
     */
    protected function getSpace($spaceId) : ?Space
    {
        /** @var Space $space */
        $space = $this->grantService->getCurrentSpace();

        // TODO: revisit null case
        if($spaceId !== null && $this->grantService->hasCurrentUserGrant('persistence-security-space')) {
            $space = $this->em->getRepository(Space::class)->find($spaceId);
        }

        return $space;
    }

    /**
     * @param $className
     * @param $entities
     * @return array
     */
    protected function getRelatedData($className, $entities) : array
    {
        $relatedData = [];
        if (!empty($entities)) {
            $classMetadata = $this->em->getClassMetadata($className);
            $associationMappings = $classMetadata->getAssociationMappings();

            foreach ($entities as $entity) {

                $relatedData[$entity->getId()]['sum'] = 0;

                if (!empty($associationMappings)) {
                    foreach ($associationMappings as $associationMapping) {
                        $mappedBy = null;
                        $id = null;
                        $ids = null;
                        if ($associationMapping['type'] === ClassMetadataInfo::MANY_TO_MANY) {
                            $getter = $entity->{'get' . ucfirst($associationMapping['fieldName'])}();

                            if ($associationMapping['targetEntity'] === Role::class) {
                                $getter = $entity->{'getRoleObjects'}();
                            }

                            if (\count($getter)) {
                                $ids = array_map(function($item){return $item->getId();} , $getter->toArray());
                            }
                        } else {
                            $mappedBy = $associationMapping['mappedBy'];
                            $id = $entity->getId();
                        }

                        if ($associationMapping['type'] === ClassMetadataInfo::MANY_TO_MANY || ($associationMapping['isOwningSide'] === false && ($associationMapping['type'] === ClassMetadataInfo::ONE_TO_MANY || $associationMapping['type'] === ClassMetadataInfo::ONE_TO_ONE))) {
                            $targetEntityName = explode('\\',$associationMapping['targetEntity']);
                            $targetEntityName = lcfirst(end($targetEntityName)) . 's';

                            $targetEntityRepo = $this->em->getRepository($associationMapping['targetEntity']);

                            $targetEntities = [];
                            if($targetEntityRepo instanceof RelatedInfoInterface) {
                                $targetEntities = $targetEntityRepo->getRelatedData($this->grantService->getCurrentSpace(), null, $mappedBy, $id, $ids);
                            }

                            $count = 0;
                            if (!empty($targetEntities)) {
                                $count = \count($targetEntities);
                            }

                            $hasAccessToView = $this->grantService->hasCurrentUserEntityGrant($associationMapping['targetEntity'], Grant::$LEVEL_VIEW);

                            if ($hasAccessToView) {
                                $targetEntities = [];
                                if($targetEntityRepo instanceof RelatedInfoInterface) {
                                    $targetEntities = $targetEntityRepo->getRelatedData($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants($associationMapping['targetEntity']), $mappedBy, $id, $ids);
                                }
                            } else {
                                $targetEntities = [];
                            }

                            $relatedData[$entity->getId()][] = [
                                'targetEntity' => $associationMapping['targetEntity'],
                                $targetEntityName => $targetEntities,
                                'count' => $count
                            ];

                            $relatedData[$entity->getId()]['sum'] += $count;
                        }
                    }
                }
            }
        }

        return $relatedData;
    }
}