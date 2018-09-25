<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Sorry, this email address is already in use.", groups={"api_user__add", "api_user__edit"})
 * @UniqueEntity(fields="username", message="Sorry, this username is already taken.", groups={"api_user__add", "api_user__edit"})
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_user__info", "api_user__list"})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=255)
     * @Groups({"api_user__info", "api_user__list"})
     * @Assert\NotBlank(groups={"api_user__add", "api_user__edit"})
     */
    private $first_name;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=255)
     * @Groups({"api_user__info", "api_user__list"})
     * @Assert\NotBlank(groups={"api_user__add", "api_user__edit"})
     */
    private $last_name;

    /**
     * @var string
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     * @Groups({"api_user__info", "api_user__list"})
     * @Assert\NotBlank(groups={"api_user__add", "api_user__edit"})
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=255)
     * @Assert\NotBlank(groups={"api_user__add", "api_user__edit"})
     * @Assert\Regex(
     *     pattern="/(\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*)/",
     *     message="Password of at least length 8 and it containing at least one lowercase letter, at least one uppercase letter, at least one number and at least a special character (non-word characters).",
     *     groups={"api_user__add", "api_user__edit"}
     * )
     */
    private $password;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Groups({"api_user__info", "api_user__list"})
     * @Assert\NotBlank(groups={"api_user__add", "api_user__edit"})
     * @Assert\Email(groups={"api_user__add", "api_user__edit"})
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     * @Groups({"api_user__info", "api_user__list"})
     * @Assert\NotBlank(groups={"api_user__add", "api_user__edit"})
     * @Assert\Regex(
     *     pattern="/(\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$)/",
     *     groups={"api_user__add", "api_user__edit"}
     * )
     */
    private $phone;

    /**
     * @var bool
     * @ORM\Column(name="enabled", type="boolean")
     * @Groups({"api_user__info", "api_user__list"})
     * @Assert\NotNull(groups={"api_user__add", "api_user__edit"})
     */
    private $enabled;

    /**
     * @var \Datetime
     * @ORM\Column(name="last_activity_at", type="datetime")
     * @Groups({"api_user__info", "api_user__list"})
     */
    protected $lastActivityAt;

    /**
     * @var string|null
     * @ORM\Column(name="confirmation_token", type="datetime", nullable=true)
     */
    protected $confirmationToken;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * @var array $roles
     * @ORM\Column(name="roles", type="json_array", length=500, nullable=false)
     * @Groups({"api_user__list"})
     * @Assert\NotBlank(groups={"api_user__add", "api_user__edit"}),
     * @Assert\All({
     *     @Assert\NotBlank(groups={"api_user__add", "api_user__edit"}),
     *     @Assert\NotNull(groups={"api_user__add", "api_user__edit"}),
     *     @Assert\Choice(callback={"App\Model\UserRole", "getRoles"}, groups={"api_user__add", "api_user__edit"})
     * })
     */
    private $roles = [];

    /**
     * @var string
     * @ORM\Column(name="password_recovery_hash", type="string", length=255, nullable=true)
     */
    private $passwordRecoveryHash = '';

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->enabled = true;
    }

    /**
     * @return null|string
     */
    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
// see section on salt below
// $this->salt,
            $this->enabled,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
// see section on salt below
// $this->salt,
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
        return $this;
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
        return $this;
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
     * @return User
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
        return $this;
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
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
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
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
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
        return $this;
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
        return $this;
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
        return $this;
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
     * @return Bool Whether the user is active or not
     */
    public function isActiveNow()
    {
        // Delay during wich the user will be considered as still active
        $delay = new \DateTime('2 minutes ago');

        return ( $this->getLastActivityAt() > $delay );
    }

    /**
     * @param string $role
     * @return User
     */
    public function addRole($role)
    {
        $roles = $this->getRoles();
        if (!$this->hasRole($role)) {
            $roles[] = $role;
        }
        $this->setRoles($roles);
        return $this;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * @return string
     */
    public function getPasswordRecoveryHash(): string
    {
        return $this->passwordRecoveryHash;
    }

    /**
     * @param string $passwordRecoveryHash
     */
    public function setPasswordRecoveryHash(string $passwordRecoveryHash): void
    {
        $this->passwordRecoveryHash = $passwordRecoveryHash;
    }
}
