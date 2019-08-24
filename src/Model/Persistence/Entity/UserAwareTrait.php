<?php

namespace App\Model\Persistence\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\User;

trait UserAwareTrait
{
    /**
     * @var User
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="id_created_user", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $createdBy;

    /**
     * @var User
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="id_updated_user", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $updatedBy;

    /** ======================================================== **
     * Getters, setters.
     ** ======================================================== **/

    /**
     * @return User|null
     */
    public function getCreatedBy() : ?User
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     */
    public function setCreatedBy($createdBy) : void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return User|null
     */
    public function getUpdatedBy() : ?User
    {
        return $this->updatedBy;
    }

    /**
     * @param User $updatedBy
     */
    public function setUpdatedBy($updatedBy) : void
    {
        $this->updatedBy = $updatedBy;
    }
}
