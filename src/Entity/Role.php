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
     * @ORM\JoinColumn(name="id_space", referencedColumnName="id", nullable=true)
     * @Groups({
     *     "api_admin_role_grid",
     *     "api_admin_role_list",
     *     "api_admin_role_get"
     * })
     */
    private $space;

    /**
     * @ORM\ManyToMany(targetEntity="Permission", mappedBy="roles", cascade={"persist", "remove"})
     * @Groups({
     *     "api_dashboard_space_user_get",
     *     "api_profile_me",
     *     "api_admin_role_get"
     * })
     */
    protected $permissions;

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
     * Permission constructor.
     */
    public function __construct()
    {
        $this->permissions    = new ArrayCollection();
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
     * @return mixed
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param Permission[] $permissions
     */
    public function setPermissions($permissions): void
    {
        $this->permissions = $permissions;

        foreach ($permissions as $permission) {
            $permission->addRole($this);
        }
    }

    /**
     * @param Permission $permission
     */
    public function addPermission($permission)
    {
        $permission->addRole($this);
        $this->permissions[] = $permission;
    }

    /**
     * @param Permission $permission
     */
    public function removePermission(Permission $permission)
    {
        $this->permissions->removeElement($permission);
        $permission->removeRole($this);
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
}
