<?php
namespace App\Api\V1\Common\Service;

use App\Annotation\ValidationSerializedName;
use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Util\Mailer;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
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
     * BaseService constructor.
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @param Mailer $mailer
     * @param ValidatorInterface $validator
     * @param Security $security
     * @param Reader $reader
     */
    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder,
        Mailer $mailer,
        ValidatorInterface $validator,
        Security $security,
        Reader $reader
    ) {
        $this->em        = $em;
        $this->encoder   = $encoder;
        $this->mailer    = $mailer;
        $this->validator = $validator;
        $this->security  = $security;
        $this->reader    = $reader;
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
                    get_class($entity),
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
}
