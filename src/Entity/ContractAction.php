<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ContractAction
 *
 * @ORM\Entity(repositoryClass="App\Repository\ContractActionRepository")
 * @ORM\Table(name="tbl_contract_action")
 * @Grid(
 *     api_admin_contract_action_grid={
 *          {"id", "number", true, true, "ca.id"},
 *          {"type", "enum", true, true, "ca.type", {"\App\Model\ContractType", "getTypeDefaultNames"}},
 *          {"start", "string", true, true, "ca.start"},
 *          {"end", "string", true, true, "ca.end"},
 *     }
 * )
 */
class ContractAction
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_contract_action_grid",
     *     "api_admin_contract_action_list",
     *     "api_admin_contract_action_get"
     * })
     */
    private $id;

    /**
     * @var Contract
     * @ORM\ManyToOne(targetEntity="App\Entity\Contract")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_contract", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_contract_action_grid", "api_admin_contract_action_list", "api_admin_contract_action_get"})
     */
    private $contract;

    /**
     * @var int
     * @ORM\Column(name="type", type="smallint")
     * @Groups({
     *      "api_admin_contract_action_list",
     *      "api_admin_contract_action_get",
     * })
     */
    private $type;

    /**
     * @var \DateTime
     * @ORM\Column(name="start", type="datetime")
     * @Groups({
     *     "api_admin_contract_action_grid",
     *     "api_admin_contract_action_list",
     *     "api_admin_contract_action_get"
     * })
     */
    private $start;

    /**
     * @var \DateTime
     * @ORM\Column(name="end", type="datetime", nullable=true)
     * @Groups({
     *     "api_admin_contract_action_grid",
     *     "api_admin_contract_action_list",
     *     "api_admin_contract_action_get"
     * })
     */
    private $end;


    /**
     * @var int
     * @ORM\Column(name="state", type="integer", length=1)
     * @Groups({
     *     "api_admin_contract_action_grid",
     *     "api_admin_contract_action_list",
     *     "api_admin_contract_action_get"
     * })
     */
    private $state;

    /**
     * @var FacilityBed
     * @ORM\ManyToOne(targetEntity="App\Entity\FacilityBed")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_facility_bed", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_admin_contract_action_grid",
     *     "api_admin_contract_action_list",
     *     "api_admin_contract_action_get"
     * })
     */
    private $facilityBed;

    /**
     * @var ApartmentBed
     * @ORM\ManyToOne(targetEntity="App\Entity\ApartmentBed")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_apartment_bed", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_admin_contract_action_grid",
     *     "api_admin_contract_action_list",
     *     "api_admin_contract_action_get"
     * })
     */
    private $apartmentBed;

    /**
     * @var Region
     * @ORM\ManyToOne(targetEntity="App\Entity\Region")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_region", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_admin_contract_action_grid",
     *     "api_admin_contract_action_list",
     *     "api_admin_contract_action_get"
     * })
     */
    private $region;

    /**
     * @var CityStateZip
     * @ORM\ManyToOne(targetEntity="App\Entity\CityStateZip")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_csz", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_admin_contract_action_grid",
     *     "api_admin_contract_action_list",
     *     "api_admin_contract_action_get"
     * })
     */
    private $csz;

    /**
     * @var string
     * @ORM\Column(name="address", type="string", length=256, nullable=true)
     * @Groups({
     *     "api_admin_contract_action_grid",
     *     "api_admin_contract_action_list",
     *     "api_admin_contract_action_get"
     * })
     */
    private $address;

    /**
     * @var bool
     * @ORM\Column(name="dnr", type="boolean", nullable=true)
     * @Groups({
     *     "api_admin_contract_action_grid",
     *     "api_admin_contract_action_list",
     *     "api_admin_contract_action_get"
     * })
     */
    private $dnr;

    /**
     * @var bool
     * @ORM\Column(name="polst", type="boolean", nullable=true)
     * @Groups({
     *     "api_admin_contract_action_grid",
     *     "api_admin_contract_action_list",
     *     "api_admin_contract_action_get"
     * })
     */
    private $polst;

    /**
     * @var bool
     * @ORM\Column(name="ambulatory", type="boolean", nullable=true)
     * @Groups({
     *     "api_admin_contract_action_grid",
     *     "api_admin_contract_action_list",
     *     "api_admin_contract_action_get"
     * })
     */
    private $ambulatory;

    /**
     * @var int
     * @ORM\Column(name="care_group", type="smallint", nullable=true)
     * @Groups({
     *     "api_admin_contract_action_grid",
     *     "api_admin_contract_action_list",
     *     "api_admin_contract_action_get"
     * })
     */
    private $careGroup;

    /**
     * @var CareLevel
     * @ORM\ManyToOne(targetEntity="App\Entity\CareLevel")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_care_level", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_admin_contract_action_grid",
     *     "api_admin_contract_action_list",
     *     "api_admin_contract_action_get"
     * })
     */
    private $careLevel;

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
     * @return Contract|null
     */
    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    /**
     * @param Contract|null $contract
     * @return ContractAction
     */
    public function setContract(?Contract $contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStart(): \DateTime
    {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     */
    public function setStart($start): void
    {
        $this->start = $start;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): \DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     */
    public function setEnd($end): void
    {
        $this->end = $end;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState($state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return FacilityBed|null
     */
    public function getFacilityBed(): ?FacilityBed
    {
        return $this->facilityBed;
    }

    /**
     * @param FacilityBed|null $facilityBed
     */
    public function setFacilityBed(?FacilityBed $facilityBed): void
    {
        $this->facilityBed = $facilityBed;
    }

    /**
     * @return ApartmentBed|null
     */
    public function getApartmentBed(): ?ApartmentBed
    {
        return $this->apartmentBed;
    }

    /**
     * @param ApartmentBed|null $apartmentBed
     */
    public function setApartmentBed(?ApartmentBed $apartmentBed): void
    {
        $this->apartmentBed = $apartmentBed;
    }

    /**
     * @return Region|null
     */
    public function getRegion(): ?Region
    {
        return $this->region;
    }

    /**
     * @param Region|null $region
     */
    public function setRegion(?Region $region): void
    {
        $this->region = $region;
    }

    /**
     * @return CityStateZip|null
     */
    public function getCsz(): ?CityStateZip
    {
        return $this->csz;
    }

    /**
     * @param CityStateZip|null $csz
     */
    public function setCsz(?CityStateZip $csz): void
    {
        $this->csz = $csz;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     */
    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return bool|null
     */
    public function isDnr(): ?bool
    {
        return $this->dnr;
    }

    /**
     * @param bool|null $dnr
     */
    public function setDnr(?bool $dnr): void
    {
        $this->dnr = $dnr;
    }

    /**
     * @return bool|null
     */
    public function isPolst(): ?bool
    {
        return $this->polst;
    }

    /**
     * @param bool|null $polst
     */
    public function setPolst(?bool $polst):void
    {
        $this->polst = $polst;
    }

    /**
     * @return bool|null
     */
    public function isAmbulatory(): ?bool
    {
        return $this->ambulatory;
    }

    /**
     * @param bool|null $ambulatory
     */
    public function setAmbulatory(?bool $ambulatory): void
    {
        $this->ambulatory = $ambulatory;
    }

    /**
     * @return int|null
     */
    public function getCareGroup(): ?int
    {
        return $this->careGroup;
    }

    /**
     * @param int|null $careGroup
     */
    public function setCareGroup(?int $careGroup): void
    {
        $this->careGroup = $careGroup;
    }

    /**
     * @return CareLevel|null
     */
    public function getCareLevel(): ?CareLevel
    {
        return $this->careLevel;
    }

    /**
     * @param CareLevel|null $careLevel
     */
    public function setCareLevel(?CareLevel $careLevel): void
    {
        $this->careLevel = $careLevel;
    }
}