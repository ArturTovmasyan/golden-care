<?php

namespace App\Entity;

use App\Model\ContractState;
use App\Model\Persistence\Entity\ResidentCareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="tbl_contract_region_option")
 * @ORM\Entity(repositoryClass="App\Repository\ContractRegionOptionRepository")
 */
class ContractRegionOption
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
     * @ORM\OneToOne(targetEntity="App\Entity\Contract", inversedBy="contractRegionOption")
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
     * @Groups({"api_admin_contract_grid", "api_admin_contract_list", "api_admin_contract_get"})
     */
    private $state = ContractState::ACTIVE;

    /**
     * @var Region
     * @ORM\ManyToOne(targetEntity="App\Entity\Region")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_region", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_contract_add",
     *     "api_admin_contract_edit"
     * })
     * @Groups({
     *      "api_admin_contract_grid",
     *      "api_admin_contract_list",
     *      "api_admin_contract_get"
     * })
     */
    private $region;

    /**
     * @var CityStateZip
     * @ORM\ManyToOne(targetEntity="App\Entity\CityStateZip")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_csz", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_contract_add",
     *     "api_admin_contract_edit"
     * })
     * @Groups({
     *      "api_admin_contract_grid",
     *      "api_admin_contract_list",
     *      "api_admin_contract_get",
     * })
     */
    private $csz;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_contract_add",
     *     "api_admin_contract_edit"
     * })
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "Address cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_contract_add", "api_admin_contract_edit"}
     * )
     * @ORM\Column(name="address", type="string", length=200)
     * @Groups({
     *     "api_admin_contract_grid",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get"
     * })
     */
    private $address;

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

    public function setState($state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return Region
     */
    public function getRegion(): Region
    {
        return $this->region;
    }

    /**
     * @param Region $region
     */
    public function setRegion(Region $region): void
    {
        $this->region = $region;
    }

    /**
     * @return CityStateZip
     */
    public function getCsz(): CityStateZip
    {
        return $this->csz;
    }

    /**
     * @param CityStateZip $csz
     */
    public function setCsz(CityStateZip $csz): void
    {
        $this->csz = $csz;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }
}
