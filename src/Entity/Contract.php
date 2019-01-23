<?php

namespace App\Entity;

use App\Model\ContractType;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Contract
 *
 * @ORM\Entity(repositoryClass="App\Repository\ContractRepository")
 * @ORM\Table(name="tbl_contract")
 * @Grid(
 *     api_admin_contract_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "sortable"   = true,
 *              "filterable" = true,
 *              "field"      = "c.id"
 *          },
 *          {
 *              "id"         = "start",
 *              "type"       = "date",
 *              "sortable"   = true,
 *              "filterable" = true,
 *              "field"      = "c.start"
 *          },
 *          {
 *              "id"         = "end",
 *              "type"       = "date",
 *              "sortable"   = true,
 *              "filterable" = true,
 *              "field"      = "c.end"
 *          },
 *          {
 *              "id"         = "type",
 *              "type"       = "enum",
 *              "sortable"   = true,
 *              "filterable" = true,
 *              "field"      = "c.type",
 *              "values"     = "\App\Model\ContractType::getTypeDefaultNames"
 *          }
 *     }
 * )
 */
class Contract
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_contract_grid",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a Resident", groups={"api_admin_contract_add", "api_admin_contract_edit"})
     * @Groups({"api_admin_contract_grid", "api_admin_contract_list", "api_admin_contract_get"})
     */
    private $resident;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={"api_admin_contract_add", "api_admin_contract_edit"})
     * @Assert\DateTime(groups={"api_admin_contract_add", "api_admin_contract_edit"})
     * @ORM\Column(name="start", type="datetime")
     * @Groups({
     *     "api_admin_contract_grid",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active"
     * })
     */
    private $start;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={"api_admin_contract_add", "api_admin_contract_edit"})
     * @ORM\Column(name="end", type="datetime", nullable=true)
     * @Groups({
     *     "api_admin_contract_grid",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active"
     * })
     */
    private $end;

    /**
     * @var int
     * @ORM\Column(name="type", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\ContractType","getTypeValues"},
     *     groups={
     *          "api_admin_contract_add"
     *     }
     * )
     * @Groups({
     *      "api_admin_contract_list",
     *      "api_admin_contract_get",
     *      "api_admin_contract_get_active"
     * })
     */
    private $type;

    /**
     * @var ContractFacilityOption
     * @ORM\OneToOne(targetEntity="App\Entity\ContractFacilityOption", mappedBy="contract")
     */
    private $contractFacilityOption;

    /**
     * @var ContractApartmentOption
     * @ORM\OneToOne(targetEntity="App\Entity\ContractApartmentOption", mappedBy="contract")
     */
    private $contractApartmentOption;

    /**
     * @var ContractRegionOption
     * @ORM\OneToOne(targetEntity="App\Entity\ContractRegionOption", mappedBy="contract")
     */
    private $contractRegionOption;

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("number")
     * @Groups({"api_admin_resident_list_by_params", "api_admin_contract_list_by_params"})
     */
    public function getOptionNumber(): ?string
    {
        switch($this->type) {
            case ContractType::TYPE_FACILITY:
                return $this->contractFacilityOption->getFacilityBed()->getNumber();
            case ContractType::TYPE_APARTMENT:
                return $this->contractApartmentOption->getApartmentBed()->getNumber();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("option")
     * @Groups({
     *      "api_admin_contract_list",
     *      "api_admin_contract_get",
     *      "api_admin_contract_get_active"
     * })
     */
    public function getOption()
    {
        switch($this->type) {
            case ContractType::TYPE_FACILITY:
                return $this->contractFacilityOption;
            case ContractType::TYPE_APARTMENT:
                return $this->contractApartmentOption;
            case ContractType::TYPE_REGION:
                return $this->contractRegionOption;
        }

        return null;
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
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Resident|null
     */
    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    /**
     * @param Resident|null $resident
     * @return Contract
     */
    public function setResident(?Resident $resident): self
    {
        $this->resident = $resident;

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
     * @return \DateTime|null
     */
    public function getEnd(): ?\DateTime
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }
}
