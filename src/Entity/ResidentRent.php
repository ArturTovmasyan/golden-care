<?php

namespace App\Entity;

use App\Model\RentPeriod;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentRent
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentRentRepository")
 * @ORM\Table(name="tbl_resident_rent")
 * @Grid(
 *     api_admin_resident_rent_grid={
 *          {"id", "number", true, true, "rr.id"},
 *          {"start", "date", true, true, "rr.start"},
 *          {"end", "date", true, true, "rr.end"},
 *          {"period", "enum", true, true, "rr.period", {"\App\Model\RentPeriod", "getTypeDefaultNames"}},
 *          {"amount", "number", true, true, "rr.amount"},
 *          {"notes", "string", true, true, "rr.notes"},
 *          {"sources", "string", true, true, "rr.source"},
 *     }
 * )
 */
class ResidentRent
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_rent_grid",
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_rent_grid", "api_admin_resident_rent_list", "api_admin_resident_rent_get"})
     */
    private $resident;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"})
     * @Assert\DateTime(groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"})
     * @ORM\Column(name="start", type="datetime")
     * @Groups({"api_admin_resident_rent_grid", "api_admin_resident_rent_list", "api_admin_resident_rent_get"})
     */
    private $start;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"})
     * @ORM\Column(name="end", type="datetime", nullable=true)
     * @Groups({
     *     "api_admin_resident_rent_grid",
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get"
     * })
     */
    private $end;

    /**
     * @var int
     * @Assert\NotBlank(groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"})
     * @Assert\Choice(
     *     callback={"App\Model\RentPeriod","getTypeValues"},
     *     groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"}
     * )
     * @ORM\Column(name="rent_period", type="integer", length=1)
     * @Groups({
     *     "api_admin_resident_rent_grid",
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get"
     * })
     */
    private $period = RentPeriod::MONTHLY;

    /**
     * @var int $amount
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\NotBlank(groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"})
     * @Assert\Regex(
     *      pattern="/(^0$)|(^[1-9][0-9]*$)|(^[0-9]+(\.[0-9]{1,2})$)/",
     *      message="The value {{ value }} is not a valid type. Try to add something like '2000, 0.55, 100.34'",
     *      groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"}
     * )
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"}
     * )
     * @Groups({"api_admin_resident_rent_grid", "api_admin_resident_rent_list", "api_admin_resident_rent_get"})
     */
    private $amount;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"}
     * )
     * @Groups({"api_admin_resident_rent_grid", "api_admin_resident_rent_list", "api_admin_resident_rent_get"})
     */
    private $notes;

    /**
     * @var array $source
     * @ORM\Column(name="source", type="json_array", length=1024, nullable=true)
     * @Assert\Count(
     *      max = 10,
     *      maxMessage = "You cannot specify more than {{ limit }} sources",
     *      groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"}
     * )
     * @Groups({"api_admin_resident_rent_grid", "api_admin_resident_rent_list", "api_admin_resident_rent_get"})
     */
    private $source = [];

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
     * @return Resident|null
     */
    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    /**
     * @param Resident|null $resident
     * @return ResidentRent
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

    public function getPeriod(): ?int
    {
        return $this->period;
    }

    public function setPeriod($period): self
    {
        $this->period = $period;

        return $this;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return array
     */
    public function getSource(): array
    {
        return $this->source;
    }

    /**
     * @param array $source
     */
    public function setSource(array $source): void
    {
        $this->source = $source;
    }
}