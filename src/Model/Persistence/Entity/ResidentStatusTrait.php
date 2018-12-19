<?php

namespace App\Model\Persistence\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * Trait ResidentStatusTrait
 * @package App\Model\Persistence\Entity
 */
trait ResidentStatusTrait
{
    /**
     * @var \DateTime
     * @ORM\Column(name="date_admitted", type="datetime", nullable=true)
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_contract_get_active"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_resident_add"
     * })
     */
    private $dateAdmitted;

    /**
     * @var int
     * @ORM\Column(name="state", type="smallint", nullable=false)
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_contract_get_active"
     * })
     * @Assert\Choice({1, 2}, groups={
     *     "api_admin_resident_add"
     * })
     */
    private $state = \App\Model\Resident::ACTIVE;

    /**
     * @return \DateTime
     */
    public function getDateAdmitted(): \DateTime
    {
        return $this->dateAdmitted;
    }

    /**
     * @param \DateTime $dateAdmitted
     */
    public function setDateAdmitted(\DateTime $dateAdmitted): void
    {
        $this->dateAdmitted = $dateAdmitted;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }
}
