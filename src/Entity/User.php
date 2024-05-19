<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid as Grid;
use App\Annotation\ValidationSerializedName as ValidationSerializedName;

/**
 * @ORM\Table(name="tbl_user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="This Email address is already in use.", groups={
 *     "api_admin_user_add",
 *     "api_account_signup"
 * })
 * @UniqueEntity(fields="username", message="This Username is already taken.", groups={
 *     "api_admin_user_add",
 *     "api_account_signup"
 * })
 * @UniqueEntity(
 *     fields={"space", "owner"},
 *     repositoryMethod="getUserSpaceAndOwnerCriteria",
 *     errorPath="owner",
 *     message="Owner is already in use for this space.",
 *     groups={
 *          "api_admin_user_add",
 *          "api_admin_user_edit"
 *     }
 * )
 * @Grid(
 *     api_admin_user_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "u.id"
 *          },
 *          {
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "CONCAT(u.firstName, ' ', u.lastName)",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "username",
 *              "type"       = "string",
 *              "field"      = "u.username"
 *          },
 *          {
 *              "id"         = "email",
 *              "type"       = "string",
 *              "field"      = "u.email"
 *          },
 *          {
 *              "id"         = "enabled",
 *              "type"       = "boolean",
 *              "field"      = "u.enabled"
 *          },
 *          {
 *              "id"         = "last_activity_at",
 *              "type"       = "datetime",
 *              "field"      = "u.lastActivityAt"
 *          },
 *          {
 *              "id"         = "roles",
 *              "type"       = "string",
 *              "field"      = ""
 *          },
 *          {
 *              "id"         = "facilities",
 *              "type"       = "string",
 *              "field"      = ""
 *          }
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
     *     "api_admin_user_get",
     *     "api_profile_view",
     *     "api_profile_me",
     *     "api_admin_user_invite_list",
     *     "api_admin_user_invite_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     *     "api_admin_notification_list",
     *     "api_admin_notification_get",
     *     "api_admin_change_log_list",
     *     "api_admin_change_log_get"
     * })
>>>>>>> e4d4a223 (Separated Grid and List actions.)
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={
     *     "api_admin_user_add",
     *     "api_admin_user_edit",
     *     "api_profile_edit",
     *     "api_account_signup"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "First Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_user_add",
     *          "api_admin_user_edit",
     *          "api_profile_edit",
     *          "api_account_signup"
     *      }
     * )
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_admin_user_get",
     *     "api_profile_view",
     *     "api_profile_me",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     *     "api_admin_notification_list",
     *     "api_admin_notification_get",
     *     "api_admin_change_log_list",
     *     "api_admin_change_log_get"
     * })
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={
     *     "api_admin_user_add",
     *     "api_admin_user_edit",
     *     "api_profile_edit",
     *     "api_account_signup"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Last Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_user_add",
     *          "api_admin_user_edit",
     *          "api_profile_edit",
     *          "api_account_signup"
     *      }
     * )
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_admin_user_get",
     *     "api_profile_view",
     *     "api_profile_me",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     *     "api_admin_notification_list",
     *     "api_admin_notification_get",
     *     "api_admin_change_log_list",
     *     "api_admin_change_log_get"
     * })
     */
    private $lastName;

    /**
     * @var string
     * @ORM\Column(name="username", type="string", length=255, unique=true, nullable=true)
     * @Assert\NotBlank(groups={
     *     "api_admin_user_add",
     *     "api_account_signup"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "User Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_user_add",
     *          "api_account_signup"
     *      }
     * )
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_admin_user_get",
     *     "api_profile_view",
     *     "api_profile_me"
     * })
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank(groups={
     *     "api_admin_user_add",
     *     "api_account_signup",
     *     "api_user_invite"
     * })
     * @Assert\Email(groups={
     *     "api_admin_user_add",
     *     "api_account_signup",
     *     "api_user_invite"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Email cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_user_add",
     *          "api_account_signup",
     *          "api_user_invite"
     *      }
     * )
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_admin_user_get",
     *     "api_profile_view",
     *     "api_profile_me"
     * })
     */
    private $email;

    /**
     * @var string
     * @Groups({
     *     "api_account_signup"
     * })
     * @Assert\NotBlank(groups={
     *     "api_account_signup"
     * })
     * @Assert\Regex(
     *     pattern="/^\([0-9]{3}\)\s?[0-9]{3}-[0-9]{4}$/",
     *     message="Invalid phone number format. Valid format is (XXX) XXX-XXXX.",
     *     groups={
     *         "api_account_signup"
     * })
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity="UserPhone", mappedBy="user")
     * @Assert\Valid(groups={
     *     "api_admin_user_add",
     *     "api_admin_user_edit",
     *     "api_profile_edit"
     * })
     * @Groups({
     *     "api_admin_user_get",
     *     "api_profile_view",
     *     "api_profile_me",
     *     "api_profile_edit"
     * })
     */
    private $phones;

    /**
     * @var bool
     * @ORM\Column(name="enabled", type="boolean")
     * @Assert\NotNull(groups={
     *     "api_admin_user_add",
     *     "api_admin_user_edit"
     * })
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_admin_user_get"
     * })
     */
    private $enabled;

    /**
     * @var bool
     * @ORM\Column(name="completed", type="boolean")
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_admin_user_get"
     * })
     */
    private $completed;

    /**
     * @var \Datetime
     * @ORM\Column(name="last_activity_at", type="datetime")
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_admin_user_get"
     * })
     */
    protected $lastActivityAt;

    /**
     * @var string
     * @SecurityAssert\UserPassword(groups={
     *     "api_profile_change_password"
     * })
     * @Assert\NotBlank(groups={
     *     "api_profile_change_password"
     * })
     * @ValidationSerializedName(
     *     api_profile_change_password="password"
     * )
     */
    private $oldPassword;

    /**
     * @var string $plainPassword
     * @Assert\NotBlank(groups={
     *     "api_profile_change_password",
     *     "api_admin_user_add",
     *     "api_account_signup",
     *     "api_account_reset_password"
     * })
     * @Assert\NotEqualTo(
     *     propertyPath="oldPassword",
     *     groups={
     *         "api_profile_change_password"
     *     },
     *     message="The value entered cannot be the same as the current password."
     * )
     * @Assert\Regex(
     *     pattern="/(\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*)/",
     *     message="The password must be at least 8 characters long and contain at least one lowercase and one uppercase letter, one number and one special(non-word) character.",
     *     groups={
     *         "api_profile_change_password",
     *         "api_admin_user_add",
     *         "api_admin_user_edit",
     *         "api_account_signup",
     *         "api_account_reset_password"
     *     }
     * )
     * @ValidationSerializedName(
     *     api_profile_change_password="new_password",
     *     api_admin_user_add="password",
     *     api_account_reset_password="password",
     *     api_account_signup="password"
     * )
     */
    private $plainPassword;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *         "api_profile_change_password",
     *         "api_admin_user_add",
     *         "api_account_signup",
     *         "api_account_reset_password"
     * })
     * @Assert\EqualTo(
     *     propertyPath="plainPassword",
     *     groups={
     *         "api_profile_change_password",
     *         "api_admin_user_add",
     *         "api_admin_user_edit",
     *         "api_account_signup",
     *         "api_account_reset_password"
     *     },
     *     message="The value entered must be the same as the new password."
     * )
     * @ValidationSerializedName(
     *     api_profile_change_password="re_new_password",
     *     api_admin_user_add="re_password",
     *     api_admin_user_edit="re_password",
     *     api_account_reset_password="re_password",
     *     api_account_signup="re_password"
     * )
     */
    private $confirmPassword;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
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
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_user_role",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_role", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @Groups({
     *     "api_admin_user_get",
     * })
     */
    private $roles;

    /**
     * @var array $grants
     * @ORM\Column(name="grants", type="json_array", nullable=false)
     * @Groups({
     *     "api_admin_user_get",
     * })
     */
    private $grants = [];

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_space_add",
     *     "api_admin_space_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_user_list",
     *     "api_admin_user_get",
     *     "api_profile_view",
     *     "api_profile_me"
     * })
     */
    private $space;

    /* PermissionDTO */
    /** @var array|mixed
     * @Groups({
     *     "api_profile_view",
     *     "api_profile_me"
     * })
     */
    private $permissions;

    /**
     * @var bool
     * @ORM\Column(name="is_owner", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_admin_user_get",
     *     "api_profile_view",
     *     "api_profile_me"
     * })
     * @Assert\GreaterThanOrEqual(value=0, groups={
     *      "api_admin_user_add",
     *      "api_admin_user_edit",
     * })
     */
    private $owner;

    /**
     * @var bool
     * @ORM\Column(name="license_accepted", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_user_grid",
     *     "api_admin_user_list",
     *     "api_admin_user_get",
     *     "api_profile_view",
     *     "api_profile_me"
     * })
     */
    private $licenseAccepted;

    /**
     * @var UserImage
     * @ORM\OneToOne(targetEntity="App\Entity\UserImage", mappedBy="user", cascade={"remove", "persist"})
     * @Groups({
     *     "api_profile_view",
     *     "api_profile_me"
     * })
     */
    private $image;

    /**
     * @var string
     * @ORM\Column(name="topic", type="string", length=200, nullable=true)
     */
    private $topic;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Activity", mappedBy="assignTo", cascade={"remove", "persist"})
     */
    private $leadActivities;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Lead", mappedBy="owner", cascade={"remove", "persist"})
     */
    private $leads;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Notification", mappedBy="users", cascade={"persist"})
     */
    protected $notifications;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ChangeLog", mappedBy="owner", cascade={"remove", "persist"})
     */
    private $changeLogs;

    /**
     * Space constructor.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
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
    public function getId()
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
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
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
        $this->activationHash = hash('sha256', $this->email . time());
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
     * @param array $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    public function getRoleObjects()
    {
        return $this->roles;
    }

    /**
     * @return array
     */
    public function getGrants(): ?array
    {
        return $this->grants;
    }

    /**
     * @param array $grants
     */
    public function setGrants(?array $grants): void
    {
        $this->grants = $grants;
    }

    /**
     * @return Space
     */
    public function getSpace(): ?Space
    {
        return $this->space;
    }

    /**
     * @param Space $space
     */
    public function setSpace(?Space $space): void
    {
        $this->space = $space;
    }

    /**
     * @return array|mixed
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param array|mixed $permissions
     */
    public function setPermissions($permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     * @return bool
     */
    public function isOwner(): ?bool
    {
        return $this->owner;
    }

    /**
     * @param bool $owner
     */
    public function setOwner(?bool $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return bool
     */
    public function isLicenseAccepted(): ?bool
    {
        return $this->licenseAccepted;
    }

    /**
     * @param bool $licenseAccepted
     */
    public function setLicenseAccepted(?bool $licenseAccepted): void
    {
        $this->licenseAccepted = $licenseAccepted;
    }

    /**
     * @return UserImage|null
     */
    public function getImage(): ?UserImage
    {
        return $this->image;
    }

    /**
     * @param UserImage|null $image
     */
    public function setImage(?UserImage $image): void
    {
        $this->image = $image;
    }

    /**
     * @return null|string
     */
    public function getTopic(): ?string
    {
        return $this->topic;
    }

    /**
     * @param null|string $topic
     */
    public function setTopic(?string $topic): void
    {
        $this->topic = $topic;
    }

    /**
     * @return ArrayCollection
     */
    public function getLeadActivities(): ArrayCollection
    {
        return $this->leadActivities;
    }

    /**
     * @param ArrayCollection $leadActivities
     */
    public function setLeadActivities(ArrayCollection $leadActivities): void
    {
        $this->leadActivities = $leadActivities;
    }

    /**
     * @return ArrayCollection
     */
    public function getLeads(): ArrayCollection
    {
        return $this->leads;
    }

    /**
     * @param ArrayCollection $leads
     */
    public function setLeads(ArrayCollection $leads): void
    {
        $this->leads = $leads;
    }

    /**
     * @return mixed
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param mixed $notifications
     */
    public function setNotifications($notifications): void
    {
        $this->notifications = $notifications;

        /** @var Notification $notification */
        foreach ($this->notifications as $notification) {
            $notification->addUser($this);
        }
    }

    /**
     * @param Notification $notification
     */
    public function addNotification(Notification $notification): void
    {
        $notification->addUser($this);
        $this->notifications[] = $notification;
    }

    /**
     * @param Notification $notification
     */
    public function removeNotification(Notification $notification): void
    {
        $this->notifications->removeElement($notification);
        $notification->removeUser($this);
    }

    /**
     * @return ArrayCollection
     */
    public function getChangeLogs(): ArrayCollection
    {
        return $this->changeLogs;
    }

    /**
     * @param ArrayCollection $changeLogs
     */
    public function setChangeLogs(ArrayCollection $changeLogs): void
    {
        $this->changeLogs = $changeLogs;
    }
}
