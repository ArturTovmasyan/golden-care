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

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Sorry, this email address is already in use.", groups={"api_admin_user_add"})
 * @UniqueEntity(fields="username", message="Sorry, this username is already taken.", groups={"api_admin_user_add"})
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
     * @Groups({"api_admin_user_get", "api_admin_user_list", "api_dashboard_space_user_list", "api_dashboard_space_user_get", "api_dashboard_user_me"})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     * @Groups({"api_admin_user_get", "api_admin_user_list", "api_dashboard_space_user_list", "api_dashboard_space_user_get", "api_dashboard_user_me"})
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_admin_user_edit", "api_dashboard_user_edit", "api_dashboard_space_user_complete", "api_dashboard_account_signup"})
     */
    private $first_name;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     * @Groups({"api_admin_user_get", "api_admin_user_list", "api_dashboard_space_user_list", "api_dashboard_space_user_get", "api_dashboard_user_me"})
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_admin_user_edit", "api_dashboard_user_edit", "api_dashboard_space_user_complete", "api_dashboard_account_signup"})
     */
    private $last_name;

    /**
     * @var string
     * @ORM\Column(name="username", type="string", length=255, unique=true, nullable=true)
     * @Groups({"api_admin_user_get", "api_admin_user_list", "api_dashboard_space_user_list", "api_dashboard_space_user_get", "api_dashboard_user_me"})
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_dashboard_account_signup"})
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Groups({"api_admin_user_get", "api_admin_user_list", "api_dashboard_space_user_list", "api_dashboard_space_user_get", "api_dashboard_user_me"})
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_dashboard_account_signup", "api_dashboard_user_invite"})
     * @Assert\Email(groups={"api_admin_user_add", "api_dashboard_account_signup", "api_dashboard_user_invite"})
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     * @Groups({"api_admin_user_get", "api_admin_user_list", "api_dashboard_space_user_list", "api_dashboard_space_user_get", "api_dashboard_user_me"})
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_admin_user_edit", "api_dashboard_user_edit", "api_dashboard_space_user_complete"})
     * @Assert\Regex(
     *     pattern="/(\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$)/",
     *     groups={"api_admin_user_add", "api_admin_user_edit", "api_dashboard_user_edit", "api_dashboard_space_user_complete"}
     * )
     */
    private $phone;

    /**
     * @var bool
     * @ORM\Column(name="enabled", type="boolean")
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_admin_user_edit", "api_dashboard_user_edit", "api_dashboard_space_user_complete"})
     * @Groups({"api_admin_user_get", "api_admin_user_list", "api_dashboard_space_user_list", "api_dashboard_space_user_get", "api_dashboard_user_me"})
     */
    private $enabled;

    /**
     * @var bool
     * @ORM\Column(name="completed", type="boolean")
     * @Groups({"api_admin_user_get", "api_admin_user_list", "api_dashboard_space_user_list", "api_dashboard_space_user_get", "api_dashboard_user_me"})
     */
    private $completed;

    /**
     * @var \Datetime
     * @ORM\Column(name="last_activity_at", type="datetime")
     * @Groups({"api_admin_user_get", "api_admin_user_list", "api_dashboard_space_user_list", "api_dashboard_space_user_get", "api_dashboard_user_me"})
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_dashboard_account_signup", "api_dashboard_user_invite"})
     */
    protected $lastActivityAt;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_dashboard_user_change_password"})
     * @SecurityAssert\UserPassword(groups={"api_dashboard_user_change_password"})
     */
    private $oldPassword;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_dashboard_user_change_password", "api_admin_user_add", "api_dashboard_account_signup", "api_dashboard_account_forgot_password_confirm_password"})
     * @Assert\EqualTo(
     *     propertyPath="plainPassword",
     *     groups={"api_dashboard_user_change_password", "api_admin_user_add", "api_dashboard_account_signup", "api_dashboard_account_forgot_password_confirm_password"},
     *     message="This value should be equal to password"
     * )
     */
    private $confirmPassword;

    /**
     * @var string $plainPassword
     * @Assert\NotBlank(groups={"api_dashboard_user_change_password", "api_admin_user_add", "api_dashboard_account_signup", "api_dashboard_account_forgot_password_confirm_password"})
     * @Assert\NotEqualTo(propertyPath="oldPassword", groups={"api_dashboard_user_change_password"}, message="This value should not be equal to old password")
     * @Assert\Regex(
     *     pattern="/(\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*)/",
     *     message="Password of at least length 8 and it containing at least one lowercase letter, at least one uppercase letter, at least one number and at least a special character (non-word characters).",
     *     groups={"api_dashboard_user_change_password", "api_admin_user_add", "api_dashboard_account_signup", "api_dashboard_account_forgot_password_confirm_password"}
     * )
     */
    private $plainPassword;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"api_admin_user_add", "api_admin_user_reset_password", "api_dashboard_user_change_password", "api_dashboard_account_signup"})
     * @Assert\Regex(
     *     pattern="/(\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*)/",
     *     message="Password of at least length 8 and it containing at least one lowercase letter, at least one uppercase letter, at least one number and at least a special character (non-word characters).",
     *     groups={"api_admin_user_add", "api_dashboard_account_signup", "api_dashboard_account_forgot_password_confirm_password"}
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
     * @var int
     * @ORM\Column(name="password_mistakes", type="integer", nullable=true, options={"default" = 0})
     */
    private $passwordMistakes;

    /**
     * @var \DateTime
     * @ORM\Column(name="password_blocked_at", type="datetime", nullable=true)
     */
    private $passwordBlockedAt;

    /**
     * @todo remove after investigate jms listener
     * @deprecated
     * @var array
     */
    private $roles = [];

    /**
     * @var @ORM\OneToMany(targetEntity="SpaceUserRole", mappedBy="user", cascade={"persist", "remove"})
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
        return $this->first_name;
    }

    /**
     * @param string $first_name
     * @return User
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param string $last_name
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
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
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
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
        $this->passwordRecoveryHash = $this->passwordRecoveryHash = hash('sha256', $this->email . time());
    }

    /**
     * @return int
     */
    public function getPasswordMistakes(): int
    {
        return $this->passwordMistakes;
    }

    /**
     * @param int $passwordMistakes
     */
    public function setPasswordMistakes(int $passwordMistakes): void
    {
        $this->passwordMistakes = $passwordMistakes;
    }

    /**
     *
     */
    public function incrementPasswordMistakes(): void
    {
        $this->passwordMistakes += 1;
    }

    /**
     * @return null|\DateTime
     */
    public function getPasswordBlockedAt()
    {
        return $this->passwordBlockedAt;
    }

    /**
     * @param null|\DateTime $passwordBlockedAt
     */
    public function setPasswordBlockedAt(\DateTime $passwordBlockedAt = null): void
    {
        $this->passwordBlockedAt = $passwordBlockedAt;
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
}
