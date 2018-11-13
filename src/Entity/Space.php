<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="tbl_space")
 * @ORM\Entity(repositoryClass="App\Repository\SpaceRepository")
 * @UniqueEntity(fields="name", message="Sorry, this name is already in use.", groups={"api_dashboard_space_edit"})
 */
class Space
{
    use TimeAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_admin_role_list", "api_dashboard_space_user_get", "api_profile_me", "api_dashboard_space_get"})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", unique=true, length=255)
     * @Groups({"api_admin_role_list", "api_dashboard_space_user_get", "api_profile_me", "api_dashboard_space_get"})
     * @Assert\NotBlank(groups={"api_dashboard_space_edit"})
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="SpaceUserRole", mappedBy="space", cascade={"persist", "remove"})
     */
    protected $spaceUserRoles;

    /**
     * Space constructor.
     */
    public function __construct()
    {
        $this->spaceUserRoles = new ArrayCollection();
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
    public function getSpaceUserRoles()
    {
        return $this->spaceUserRoles;
    }
}
