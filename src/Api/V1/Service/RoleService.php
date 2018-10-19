<?php
namespace App\Api\V1\Service;


use App\Api\V1\Service\Exception\DuplicateSpaceUserRoleException;
use App\Api\V1\Service\Exception\DuplicateUserException;
use App\Api\V1\Service\Exception\IncorrectRepeatPasswordException;
use App\Api\V1\Service\Exception\InvalidConfirmationTokenException;
use App\Api\V1\Service\Exception\RoleNotFoundException;
use App\Api\V1\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Service\Exception\SpaceUserNotFoundException;
use App\Api\V1\Service\Exception\SpaceUserRoleNotFoundException;
use App\Api\V1\Service\Exception\SystemErrorException;
use App\Api\V1\Service\Exception\UserAlreadyJoinedException;
use App\Api\V1\Service\Exception\UserNotFoundException;
use App\Api\V1\Service\Exception\ValidationException;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\SpaceUser;
use App\Entity\SpaceUserRole;
use App\Entity\User;
use App\Entity\UserLog;
use App\Model\Log;
use App\Util\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class RoleService
 * @package App\Api\V1\Service
 */
class RoleService
{
    use ControllerTrait;

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
     * UserService constructor.
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @param Mailer $mailer
     * @param ValidatorInterface $validator
     * @param Security $security
     */
    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder,
        Mailer $mailer,
        ValidatorInterface $validator,
        Security $security
    ) {
        $this->em        = $em;
        $this->encoder   = $encoder;
        $this->mailer    = $mailer;
        $this->validator = $validator;
        $this->security  = $security;
    }

    /**
     * @param Space $space
     * @param Role $role
     * @param User $user
     */
    public function addRole(Space $space, Role $role, User $user): void
    {
        $spaceUserRole = $this->em->getRepository(SpaceUserRole::class)->findOneBy(
            [
                'space' => $space,
                'role'  => $role,
                'user'  => $user,
            ]
        );

        if (!is_null($spaceUserRole)) {
            throw new DuplicateSpaceUserRoleException();
        }

        $spaceUser = $this->em->getRepository(SpaceUser::class)->findOneBy(
            [
                'space' => $space,
                'user'  => $user,
            ]
        );

        if (is_null($spaceUser)) {
            throw new SpaceUserNotFoundException();
        }

        // save relation
        $spaceUserRole = new SpaceUserRole();
        $spaceUserRole->setUser($user);
        $spaceUserRole->setRole($role);
        $spaceUserRole->setSpace($space);
        $this->em->persist($spaceUserRole);

        $this->em->flush();
    }

    /**
     * @param Space $space
     * @param Role $role
     * @param User $user
     */
    public function removeRole(Space $space, Role $role, User $user): void
    {
        $spaceUserRole = $this->em->getRepository(SpaceUserRole::class)->findOneBy(
            [
                'space' => $space,
                'role'  => $role,
                'user'  => $user,
            ]
        );

        if (is_null($spaceUserRole)) {
            throw new SpaceUserRoleNotFoundException();
        }

        $spaceUser = $this->em->getRepository(SpaceUser::class)->findOneBy(
            [
                'space' => $space,
                'user'  => $user,
            ]
        );

        if (is_null($spaceUser)) {
            throw new SpaceUserNotFoundException();
        }

        $this->em->remove($spaceUserRole);
        $this->em->flush();
    }
}