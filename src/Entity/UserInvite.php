<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="tbl_user_invite")
 * @ORM\Entity()
 * @UniqueEntity(fields="email", message="This email address was already in use.", groups={
 *     "api_admin_user_invite"
 * })
 */
class UserInvite
{
    use TimeAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
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
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
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
     */
    private $space;


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
}
