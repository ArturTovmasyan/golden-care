<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="space_user_role")
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
     * @ORM\ManyToOne(targetEntity="Space", cascade={"persist"})
     * @ORM\JoinColumn(name="space_id", referencedColumnName="id", nullable=true)
     */
    private $space;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Role", cascade={"persist"})
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id", nullable=false)
     */
    private $role;

    /**
     * @var string|null
     * @ORM\Column(name="confirmation_token", type="string", nullable=true)
     */
    protected $confirmationToken;

    /**
     * @ORM\Column(name="status", type="integer", nullable=true, options={"default" = 0})
     */
    private $status;

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

    /**
     * @return null|string
     */
    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    /**
     * @return null|string
     */
    public function cleanConfirmationToken()
    {
        $this->confirmationToken = '';
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function isAccepted()
    {
        return $this->status == \App\Model\SpaceUserRole::STATUS_ACCEPTED;
    }

    /**
     * @return bool
     */
    public function isInvited()
    {
        return $this->status == \App\Model\SpaceUserRole::STATUS_INVITED;
    }

    /**
     * Generate confirmation token for complete invitation or forgot password
     */
    public function generateConfirmationToken()
    {
        $this->confirmationToken = hash('sha256', $this->getUser()->getEmail() . rand(1, 5000) . time());
    }
}
