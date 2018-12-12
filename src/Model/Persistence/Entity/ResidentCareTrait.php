<?php

namespace App\Model\Persistence\Entity;

use App\Entity\CareLevel;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * Trait ResidentCareTrait
 * @package App\Model\Persistence\Entity
 */
trait ResidentCareTrait
{
    /**
     * @var bool
     * @ORM\Column(name="dnr", type="boolean", options={"default" = 0})
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_contract_grid",
     *      "api_admin_contract_list",
     *      "api_admin_contract_get"
     * })
     */
    private $dnr = false;

    /**
     * @var bool
     * @ORM\Column(name="polst", type="boolean", options={"default" = 0})
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_contract_grid",
     *      "api_admin_contract_list",
     *      "api_admin_contract_get"
     * })
     */
    private $polst = false;

    /**
     * @var bool
     * @ORM\Column(name="ambulatory", type="boolean", options={"default" = 0})
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_contract_grid",
     *      "api_admin_contract_list",
     *      "api_admin_contract_get"
     * })
     */
    private $ambulatory = false;

    /**
     * @var int
     * @ORM\Column(name="care_group", type="smallint")
     * @Assert\Regex(
     *     pattern = "/(^[1-9][0-9]*$)/",
     *     message="Please provide a valid care group",
     *     groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit",
     *     "api_admin_contract_add",
     *     "api_admin_contract_edit"
     * }
     * )
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit",
     *     "api_admin_contract_add",
     *     "api_admin_contract_edit"
     * })
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_contract_grid",
     *      "api_admin_contract_list",
     *      "api_admin_contract_get"
     * })
     */
    private $careGroup;

    /**
     * @var CareLevel
     * @ORM\ManyToOne(targetEntity="App\Entity\CareLevel")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_care_level", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit",
     *     "api_admin_contract_add",
     *     "api_admin_contract_edit"
     * })
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_contract_grid",
     *      "api_admin_contract_list",
     *      "api_admin_contract_get"
     * })
     */
    private $careLevel;

    /**
     * @return bool
     */
    public function isDnr(): bool
    {
        return $this->dnr;
    }

    /**
     * @param bool $dnr
     */
    public function setDnr(bool $dnr): void
    {
        $this->dnr = $dnr;
    }

    /**
     * @return bool
     */
    public function isPolst(): bool
    {
        return $this->polst;
    }

    /**
     * @param bool $polst
     */
    public function setPolst(bool $polst): void
    {
        $this->polst = $polst;
    }

    /**
     * @return bool
     */
    public function isAmbulatory(): bool
    {
        return $this->ambulatory;
    }

    /**
     * @param bool $ambulatory
     */
    public function setAmbulatory(bool $ambulatory): void
    {
        $this->ambulatory = $ambulatory;
    }

    /**
     * @return int
     */
    public function getCareGroup(): int
    {
        return $this->careGroup;
    }

    /**
     * @param int $careGroup
     */
    public function setCareGroup($careGroup): void
    {
        $this->careGroup = $careGroup;
    }

    /**
     * @return CareLevel
     */
    public function getCareLevel(): CareLevel
    {
        return $this->careLevel;
    }

    /**
     * @param CareLevel $careLevel
     */
    public function setCareLevel($careLevel): void
    {
        $this->careLevel = $careLevel;
    }
}