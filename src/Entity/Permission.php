<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_permission")
 * @ORM\Entity(repositoryClass="App\Repository\PermissionRepository")
 * @Grid(
 *     api_dashboard_permission_list={
 *          {"id", "number", true, true, "p.id"},
 *          {"name", "string", true, true, "p.name"}
 *     },
 *     api_admin_permission_list={
 *          {"id", "number", true, true, "p.id"},
 *          {"name", "string", true, true, "p.name"}
 *     }
 * )
 */
class Permission
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_dashboard_space_user_get", "api_profile_me", "api_dashboard_permission_list", "api_admin_permission_list"})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     * @Groups({"api_dashboard_space_user_get", "api_profile_me", "api_dashboard_permission_list", "api_admin_permission_list"})
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="permissions", cascade={"persist", "remove"})
     * @ORM\JoinTable(
     *      name="tbl_role_permission",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_permission", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_role", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     */
    private $roles;

    /**
     * Permission constructor.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
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
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @param Role $role
     */
    public function addRole($role)
    {
        $this->roles->add($role);
    }

    /**
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }
}
