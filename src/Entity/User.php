<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid as Grid;
use App\Annotation\ValidationSerializedName as ValidationSerializedName;

/**
 * @ORM\Table(name="tbl_user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="This email address was already in use.", groups={"api_admin_user_add", "api_dashboard_account_signup"})
 * @UniqueEntity(fields="username", message="This username was already taken.", groups={"api_admin_user_add", "api_dashboard_account_signup"})
 * @Grid(
 *     api_admin_user_grid={
 *          {"id", "number", true, true, "u.id"},
 *          {"first_name", "string", true, true, "u.firstName"},
 *          {"last_name", "string", true, true, "u.lastName"},
 *          {"username", "string", true, true, "u.username"},
 *          {"email", "string", true, true, "u.email"},
 *          {"enabled", "enum", true, true, "u.enabled", {"\App\Model\Boolean", "defaultValues"}},
 *          {"completed", "enum", true, true, "u.completed", {"\App\Model\Boolean", "defaultValues"}},
 *          {"last_activity_at", "date", true, true, "u.lastActivityAt"}
 *     },
 *     api_dashboard_space_user_grid={
 *          {"id", "integer", true, true, "u.id"},
 *          {"first_name", "string", true, true, "u.firstName"},
 *          {"last_name", "string", true, true, "u.lastName"},
 *          {"username", "string", true, true, "u.username"},
 *          {"email", "string", true, true, "u.email"},
 *          {"enabled", "enum", true, true, "u.enabled", {"\App\Model\Boolean", "defaultValues"}},
 *          {"completed", "enum", true, true, "u.completed", {"\App\Model\Boolean", "defaultValues"}},
 *          {"last_activity_at", "date", true, true, "u.lastActivityAt"}
 *     }
 * )
 */
class User implements UserInterface
{
    use TimeAwareTrait;

    /**
     * Mistakes limit before block
     */
    const PASSWORD_MISTAKES_LIMIT = 3;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
<<<<<<< HEAD
     * @Groups({"api_admin_user_get", "api_admin_user_list", "api_dashboard_space_user_list", "api_dashboard_space_user_get", "api_profile_me"})
=======
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_dashboard_space_user_grid",
     *     "api_dashboard_space_user_list",
     *     "api_admin_user_get",
     *     "api_dashboard_space_user_get",
     *     "api_profile_me"
     * })
>>>>>>> e4d4a223 (Separated Grid and List actions.)
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_dashboard_space_user_grid",
     *     "api_dashboard_space_user_list",
     *     "api_admin_user_get",
     *     "api_dashboard_space_user_get",
     *     "api_profile_me"
     * })
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_admin_user_edit", "api_profile_edit", "api_dashboard_space_user_complete", "api_dashboard_account_signup"})
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_dashboard_space_user_grid",
     *     "api_dashboard_space_user_list",
     *     "api_admin_user_get",
     *     "api_dashboard_space_user_get",
     *     "api_profile_me"
     * })
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_admin_user_edit", "api_profile_edit", "api_dashboard_space_user_complete", "api_dashboard_account_signup"})
     */
    private $lastName;

    /**
     * @var string
     * @ORM\Column(name="username", type="string", length=255, unique=true, nullable=true)
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_dashboard_space_user_grid",
     *     "api_dashboard_space_user_list",
     *     "api_admin_user_get",
     *     "api_dashboard_space_user_get",
     *     "api_profile_me"
     * })
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_dashboard_account_signup"})
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_dashboard_space_user_grid",
     *     "api_dashboard_space_user_list",
     *     "api_admin_user_get",
     *     "api_dashboard_space_user_get",
     *     "api_profile_me"
     * })
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_dashboard_account_signup", "api_dashboard_user_invite"})
     * @Assert\Email(groups={"api_admin_user_add", "api_dashboard_account_signup", "api_dashboard_user_invite"})
     */
    private $email;

//    /**
//     * @var string
//     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
//     * @Groups({
//     *     "api_admin_user_grid",
//     *     "api_admin_user_list",
//     *     "api_dashboard_space_user_grid",
//     *     "api_dashboard_space_user_list",
//     *     "api_admin_user_get",
//     *     "api_dashboard_space_user_get",
//     *     "api_profile_me"
//     * })
//     * @Assert\NotBlank(groups={"api_admin_user_add", "api_admin_user_edit", "api_profile_edit", "api_dashboard_space_user_complete"})
//     * @Assert\Regex(
//     *     pattern="/(\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$)/",
//     *     groups={
//     *     "api_admin_user_add",
//     *     "api_admin_user_edit",
//     *     "api_profile_edit",
//     *     "api_dashboard_space_user_complete"
//     * })
//     */
//    private $phone;

    /**
     * @ORM\OneToMany(targetEntity="UserPhone", mappedBy="user")
     * @Groups({
     *      "api_profile_me",
     *      "api_profile_edit"
     * })
     */
    private $phones;

    /**
     * @var bool
     * @ORM\Column(name="enabled", type="boolean")
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_admin_user_edit", "api_dashboard_space_user_complete"})
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_dashboard_space_user_grid",
     *     "api_dashboard_space_user_list",
     *     "api_admin_user_get",
     *     "api_dashboard_space_user_get"
     * })
     */
    private $enabled;

    /**
     * @var bool
     * @ORM\Column(name="completed", type="boolean")
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_dashboard_space_user_grid",
     *     "api_dashboard_space_user_list",
     *     "api_admin_user_get",
     *     "api_dashboard_space_user_get"
     * })
     */
    private $completed;

    /**
     * @var \Datetime
     * @ORM\Column(name="last_activity_at", type="datetime")
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_dashboard_space_user_grid",
     *     "api_dashboard_space_user_list",
     *     "api_admin_user_get",
     *     "api_dashboard_space_user_get"
     * })
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_dashboard_account_signup", "api_dashboard_user_invite"})
     */
    protected $lastActivityAt;

    /**
     * @var string
     * @SecurityAssert\UserPassword(groups={"api_profile_change_password"})
     * @Assert\NotBlank(groups={"api_profile_change_password"})
     * @ValidationSerializedName(
     *     api_profile_change_password="password"
     * )
     */
    private $oldPassword;

    /**
     * @var string $plainPassword
     * @Assert\NotBlank(groups={"api_profile_change_password", "api_admin_user_add", "api_dashboard_account_signup", "api_dashboard_account_reset_password"})
     * @Assert\NotEqualTo(
     *     propertyPath="oldPassword",
     *     groups={
     *         "api_profile_change_password"
     *     },
     *     message="This value should not be equal to current password."
     * )
     * @Assert\Regex(
     *     pattern="/(\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*)/",
     *     message="The password must be at least 8 characters long and contain at least one lowercase letter, one uppercase letter, one number and one special character (non-word characters).",
     *     groups={"api_profile_change_password", "api_admin_user_add", "api_dashboard_account_signup", "api_dashboard_account_reset_password"}
     * )
     * @ValidationSerializedName(
     *     api_profile_change_password="new_password",
     *     api_admin_user_add="password",
     *     api_dashboard_account_reset_password="password",
     *     api_dashboard_account_signup="password"
     * )
     */
    private $plainPassword;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *         "api_profile_change_password",
     *         "api_admin_user_add",
     *         "api_dashboard_account_signup",
     *         "api_dashboard_account_reset_password"
     * })
     * @Assert\EqualTo(
     *     propertyPath="plainPassword",
     *     groups={
     *         "api_profile_change_password",
     *         "api_admin_user_add",
     *         "api_dashboard_account_signup",
     *         "api_dashboard_account_reset_password"
     *     },
     *     message="This value should match new password."
     * )
     * @ValidationSerializedName(
     *     api_profile_change_password="re_new_password",
     *     api_admin_user_add="re_password",
     *     api_dashboard_account_reset_password="re_password",
     *     api_dashboard_account_signup="re_password"
     * )
     */
    private $confirmPassword;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_admin_user_reset_password", "api_profile_change_password", "api_dashboard_account_signup"})
     * @Assert\Regex(
     *     pattern="/(\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*)/",
     *     message="The password must be at least 8 characters long and contain at least one lowercase letter, one uppercase letter, one number and one special character (non-word characters).",
     *     groups={"api_admin_user_add", "api_dashboard_account_signup", "api_dashboard_account_reset_password"}
     * )
     */
    private $password;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * @var string
     * @ORM\Column(name="password_recovery_hash", type="string", length=255, nullable=true)
     */
    private $passwordRecoveryHash = '';

    /**
     * @var string
     * @ORM\Column(name="activation_hash", type="string", length=255, nullable=true)
     */
    private $activationHash = '';

    /**
     * @var string
     * @Groups({
     *     "api_profile_edit",
     *     "api_profile_me"
     * })
     */
    private $avatar;

    /**
     * @todo remove after investigate jms listener
     * @deprecated
     * @var array
     */
    private $roles = [];

    /**
     * @var @ORM\OneToMany(targetEntity="SpaceUserRole", mappedBy="user", cascade={"persist", "remove"})
     * @Groups({"api_dashboard_space_user_get", "api_profile_me"})
     */
    private $spaceUserRoles;

    /**
     * Space constructor.
     */
    public function __construct()
    {
        $this->spaceUserRoles = new ArrayCollection();
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->enabled,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->enabled,
        ) = unserialize($serialized, ['allowed_classes' => false]);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return User
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param string $password
     */
    public function setConfirmPassword($password)
    {
        $this->confirmPassword = $password;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param mixed $phones
     */
    public function setPhones($phones): void
    {
        $this->phones = $phones;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return $this->completed;
    }

    /**
     * @param bool $completed
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
    }

    /**
     * @return \Datetime
     */
    public function getLastActivityAt(): \Datetime
    {
        return $this->lastActivityAt;
    }

    /**
     * @param \Datetime $lastActivityAt
     */
    public function setLastActivityAt(\Datetime $lastActivityAt): void
    {
        $this->lastActivityAt = $lastActivityAt;
    }

    /**
     * @return string
     */
    public function getPasswordRecoveryHash(): string
    {
        return $this->passwordRecoveryHash;
    }

    /**
     *
     */
    public function setPasswordRecoveryHash(): void
    {
        $this->passwordRecoveryHash = hash('sha256', $this->email . time());
    }

    /**
     * @return string
     */
    public function getActivationHash(): string
    {
        return $this->activationHash;
    }

    /**
     *
     */
    public function setActivationHash(): void
    {
        $this->activationHash = hash('sha256', $this->email . time());;
    }

    /**
     * @return \DateTime|null
     */
    public function getPasswordRequestedAt(): ?\DateTime
    {
        return $this->passwordRequestedAt;
    }

    /**
     * @param \DateTime|null $passwordRequestedAt
     */
    public function setPasswordRequestedAt(?\DateTime $passwordRequestedAt): void
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return string
     */
    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    /**
     * @param string $oldPassword
     */
    public function setOldPassword($oldPassword)
    {
        $this->oldPassword = $oldPassword;
    }

    /**
     * @return bool
     */
    public function isActiveNow()
    {
        // Delay during wich the user will be considered as still active
        $delay = new \DateTime('2 minutes ago');

        return ($this->getLastActivityAt() > $delay);
    }

    /**
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getRoles()
    {
        // TODO: Implement getRoles() method.
    }

    /**
     * @return mixed
     */
    public function getSpaceUserRoles()
    {
        return $this->spaceUserRoles;
    }

    /**
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

}
