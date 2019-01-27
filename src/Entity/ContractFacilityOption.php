<?php

namespace App\Entity;

use App\Model\ContractState;
use App\Model\Persistence\Entity\ResidentCareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="tbl_contract_facility_option")
 * @ORM\Entity(repositoryClass="App\Repository\ContractFacilityOptionRepository")
 */
class ContractFacilityOption
{
    use ResidentCareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Contract", inversedBy="contractFacilityOption")
     * @ORM\JoinColumn(name="id_contract", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(groups={
     *     "api_admin_contract_add"
     * })
     */
    private $contract;

    /**
     * @var int
     * @Assert\NotBlank(groups={"api_admin_contract_add", "api_admin_contract_edit"})
     * @Assert\Choice(
     *     callback={"App\Model\ContractState","getTypeValues"},
     *     groups={"api_admin_contract_add", "api_admin_contract_edit"}
     * )
     * @ORM\Column(name="state", type="integer", length=1)
     * @Groups({
     *     "api_admin_contract_grid",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active"})
     */
    private $state = ContractState::ACTIVE;

    /**
     * @var DiningRoom
     * @ORM\ManyToOne(targetEntity="App\Entity\DiningRoom")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_dining_room", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_contract_add",
     *     "api_admin_contract_edit"
     * })
     * @Groups({
     *      "api_admin_contract_grid",
     *      "api_admin_contract_list",
     *      "api_admin_contract_get",
     *      "api_admin_contract_get_active"
     * })
     */
    private $diningRoom;

    /**
     * @var FacilityBed
     * @ORM\ManyToOne(targetEntity="App\Entity\FacilityBed")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_facility_bed", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_contract_add",
     *     "api_admin_contract_edit"
     * })
     * @Groups({
     *      "api_admin_contract_grid",
     *      "api_admin_contract_list",
     *      "api_admin_contract_get",
     *      "api_admin_contract_get_active"
     * })
     * @Serializer\SerializedName("bed")
     */
    private $facilityBed;

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
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * @param mixed $contract
     */
    public function setContract($contract): void
    {
        $this->contract = $contract;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState($state): void
    {
        $this->state = $state;
    }

    /**
     * @return DiningRoom
     */
    public function getDiningRoom(): DiningRoom
    {
        return $this->diningRoom;
    }

    /**
     * @param DiningRoom $diningRoom
     */
    public function setDiningRoom($diningRoom): void
    {
        $this->diningRoom = $diningRoom;
    }

    /**
     * @return FacilityBed
     */
    public function getFacilityBed(): FacilityBed
    {
        return $this->facilityBed;
    }

    /**
     * @param FacilityBed $facilityBed
     */
    public function setFacilityBed($facilityBed): void
    {
        $this->facilityBed = $facilityBed;
    }
}
