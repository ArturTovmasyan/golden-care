<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="tbl_space_user_role")
 * @ORM\Entity(repositoryClass="App\Repository\SpaceUserRoleRepository")
 */
class SpaceUserRole
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Space", inversedBy="spaceUserRoles", cascade={"persist"})
     * @ORM\JoinColumn(name="id_space", referencedColumnName="id", nullable=true)
     * @Groups({"api_dashboard_space_user_get", "api_profile_me"})
     */
    private $space;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="spaceUserRoles", cascade={"persist"})
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="spaceUserRoles", cascade={"persist"})
     * @ORM\JoinColumn(name="id_role", referencedColumnName="id", nullable=false)
     * @Groups({"api_dashboard_space_user_get", "api_profile_me"})
     */
    private $role;

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
     * @return Space
     */
    public function getSpace()
    {
        return $this->space;
    }

    /**
     * @param Space $space
     */
    public function setSpace(Space $space): void
    {
        $this->space = $space;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return \App\Entity\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole(\App\Entity\Role $role): void
    {
        $this->role = $role;
    }
}
