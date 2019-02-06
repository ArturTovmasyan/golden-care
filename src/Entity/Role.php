<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_role")
 * @ORM\Entity(repositoryClass="App\Repository\RoleRepository")
 * @UniqueEntity(
 *     fields={"space", "name"},
 *     errorPath="name",
 *     message="This name is already in use on that space",
 *     groups={
 *          "api_admin_role_add",
 *          "api_admin_role_edit",
 *          "api_dashboard_role_add",
 *          "api_dashboard_role_edit"
 *     }
 * )
 * @Grid(
 *     api_admin_role_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "r.id"
 *          },
 *          {
 *              "id"         = "name",
 *              "type"       = "string",
 *              "field"      = "r.name"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "sortable"   = false,
 *              "filterable" = false,
 *              "field"      = "s.name"
 *          },
 *          {
 *              "id"         = "default",
 *              "type"       = "boolean",
 *              "field"      = "r.default"
 *          },
 *          {
 *              "id"         = "space_default",
 *              "type"       = "boolean",
 *              "field"      = "r.spaceDefault"
 *          }
 *     },
 *     api_dashboard_space_role_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "r.id"
 *          },
 *          {
 *              "id"         = "name",
 *              "type"       = "string",
 *              "field"      = "r.name"
 *          },
 *          {
 *              "id"         = "default",
 *              "type"       = "boolean",
 *              "field"      = "r.default"
 *          },
 *          {
 *              "id"         = "space_default",
 *              "type"       = "boolean",
 *              "field"      = "r.spaceDefault"
 *          }
 *     }
 * )
 */
class Role
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_role_grid",
     *     "api_admin_role_list",
     *     "api_admin_role_get",
     *     "api_admin_user_get",
     *     "api_dashboard_space_role_grid",
     *     "api_dashboard_space_role_list",
     *     "api_dashboard_space_role_get",
     *     "api_dashboard_space_user_get",
     *     "api_profile_me"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     * @Groups({
     *     "api_admin_role_grid",
     *     "api_admin_role_list",
     *     "api_admin_role_get",
     *     "api_dashboard_space_role_grid",
     *     "api_dashboard_space_role_list",
     *     "api_dashboard_space_role_get",
     *     "api_dashboard_space_user_get",
     *     "api_profile_me"
     * })
     * @Assert\NotBlank(groups={"api_admin_role_add", "api_admin_role_edit", "api_dashboard_role_add", "api_dashboard_role_edit"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Space", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_role_grid",
     *     "api_admin_role_list",
     *     "api_admin_role_get"
     * })
     */
    private $space;

    /**
     * @var bool
     * @ORM\Column(name="is_default", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_role_grid",
     *     "api_admin_role_list",
     *     "api_admin_role_get",
     *     "api_dashboard_space_role_grid",
     *     "api_dashboard_space_role_list",
     *     "api_dashboard_space_role_get"
     * })
     * @Assert\GreaterThanOrEqual(value=0, groups={"api_admin_role_add", "api_dashboard_role_add", "api_admin_role_edit"})
     */
    protected $default;

    /**
     * @var bool
     * @ORM\Column(name="is_space_default", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_role_grid",
     *     "api_admin_role_list",
     *     "api_admin_role_get",
     *     "api_dashboard_space_role_grid",
     *     "api_dashboard_space_role_list",
     *     "api_dashboard_space_role_get"
     * })
     * @Assert\GreaterThanOrEqual(value=0, groups={"api_admin_role_add", "api_admin_role_edit", "api_dashboard_role_add", "api_dashboard_role_edit"})
     */
    protected $spaceDefault;

    /**
     * @var @ORM\OneToMany(targetEntity="SpaceUserRole", mappedBy="role", cascade={"persist", "remove"})
     */
    protected $spaceUserRoles;

    /**
     * @var array $grants
     * @ORM\Column(name="grants", type="json_array", nullable=false)
     * @Groups({
     *     "api_admin_role_list",
     *     "api_admin_role_get",
     * })
     */
    private $grants = [];

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="roles", cascade={"persist", "remove"})
     */
    private $users;

    /**
     * Role constructor.
     */
    public function __construct()
    {
        $this->spaceUserRoles = new ArrayCollection();
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
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getSpace()
    {
        return $this->space;
    }

    /**
     * @param mixed $space
     */
    public function setSpace($space): void
    {
        $this->space = $space;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * @param bool $default
     */
    public function setDefault(bool $default): void
    {
        $this->default = $default;
    }

    /**
     * @return bool
     */
    public function isSpaceDefault(): bool
    {
        return $this->spaceDefault;
    }

    /**
     * @param bool $spaceDefault
     */
    public function setSpaceDefault(bool $spaceDefault): void
    {
        $this->spaceDefault = $spaceDefault;
    }

    /**
     * @return mixed
     */
    public function getSpaceUserRoles()
    {
        return $this->spaceUserRoles;
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
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $users
     */
    public function setUsers($users): void
    {
        $this->users = $users;
    }

}
