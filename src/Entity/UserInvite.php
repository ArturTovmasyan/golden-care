<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_user_invite")
 * @ORM\Entity(repositoryClass="App\Repository\UserInviteRepository")
 * @UniqueEntity(fields="email", message="This email address was already in use.", groups={
 *     "api_admin_user_invite"
 * })
 * @UniqueEntity(
 *     fields={"space", "owner"},
 *     repositoryMethod="getUserInviteSpaceAndOwnerCriteria",
 *     errorPath="owner",
 *     message="Owner is already invited for this space.",
 *     groups={
 *          "api_admin_user_invite"
 *     }
 * )
 * @Grid(
 *     api_admin_user_invite_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ui.id"
 *          },
 *          {
 *              "id"         = "email",
 *              "type"       = "string",
 *              "field"      = "ui.email"
 *          },
 *          {
 *              "id"         = "owner",
 *              "type"       = "boolean",
 *              "field"      = "ui.owner"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          },
 *          {
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(u.firstName, ''), ' ', COALESCE(u.lastName, ''))",
 *              "link"       = ":edit"
 *          },
 *     }
 * )
 */
class UserInvite
{
    use TimeAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_user_invite_grid",
     *     "api_admin_user_invite_list",
     *     "api_admin_user_invite_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank(groups={
     *     "api_admin_user_invite"
     * })
     * @Assert\Email(groups={
     *     "api_admin_user_invite"
     * })
     * @Groups({
     *     "api_admin_user_invite_grid",
     *     "api_admin_user_invite_list",
     *     "api_admin_user_invite_get"
     * })
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     * @Groups({
     *     "api_admin_user_invite_list",
     *     "api_admin_user_invite_get"
     * })
     */
    private $token = '';

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_user_invite"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_user_invite_list",
     *     "api_admin_user_invite_get"
     * })
     */
    private $space;

    /**
     * @var User
     * @Assert\NotNull(message = "Please select an User", groups={
     *     "api_admin_user_invite"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_user_invite_list",
     *     "api_admin_user_invite_get"
     * })
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Role", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_user_invite_role",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_user_invite", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_role", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     */
    private $roles;

    /**
     * @var bool
     * @ORM\Column(name="is_owner", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_user_invite_grid",
     *     "api_admin_user_invite_list",
     *     "api_admin_user_invite_get"
     * })
     * @Assert\GreaterThanOrEqual(value=0, groups={
     *      "api_admin_user_invite"
     * })
     */
    private $owner;

    /**
     * UserInvite constructor.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
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
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     *
     */
    public function setToken(): void
    {
        $this->token = hash('sha256', $this->email . time());
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
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}
