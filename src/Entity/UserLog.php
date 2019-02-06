<?php

namespace App\Entity;

use App\Model\Persistence\Entity\LogAwareTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="tbl_user_log")
 * @ORM\Entity(repositoryClass="App\Repository\UserLogRepository")
 */
class UserLog
{
    use LogAwareTrait;

    const LOG_TYPE_REGISTRATION        = 1;
    const LOG_TYPE_AUTHENTICATION      = 2;
    const LOG_TYPE_BLOCK_USER_PASSWORD = 3;
    const LOG_TYPE_UPDATE_PROFILE      = 4;
    const LOG_TYPE_RESET_PASSWORD      = 5;
    const LOG_TYPE_INVITATION          = 6;
    const LOG_TYPE_ACCEPT_INVITATION   = 7;
    const LOG_TYPE_REJECT_INVITATION   = 8;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Space", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $space;

    /**
     * @var int
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type;

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
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
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
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }
}
