<?php

namespace App\Entity;

use App\Model\ContractState;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="tbl_contract_apartment_option")
 * @ORM\Entity(repositoryClass="App\Repository\ContractApartmentOptionRepository")
 */
class ContractApartmentOption
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Contract", inversedBy="contractApartmentOption")
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
     * @var ApartmentBed
     * @ORM\ManyToOne(targetEntity="App\Entity\ApartmentBed")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_apartment_bed", referencedColumnName="id", onDelete="CASCADE")
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
     * @Serializer\SerializedName("bed")
     */
    private $apartmentBed;

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
     * @return ApartmentBed
     */
    public function getApartmentBed(): ApartmentBed
    {
        return $this->apartmentBed;
    }

    /**
     * @param ApartmentBed $apartmentBed
     */
    public function setApartmentBed(ApartmentBed $apartmentBed): void
    {
        $this->apartmentBed = $apartmentBed;
    }
}

