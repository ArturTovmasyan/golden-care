<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use App\Model\ResidentRentType;
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
 *          {"start", "string", true, true, "rr.start"},
 *          {"end", "string", true, true, "rr.end"},
 *          {"type", "enum", true, true, "rr.type", {"\App\Model\ResidentRentType", "getTypeDefaultNames"}},
 *          {"amount", "number", true, true, "rr.amount"},
 *          {"notes", "string", true, true, "rr.notes"},
 *          {"space", "string", true, true, "s.name"},
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
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_admin_resident_rent_grid",
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get"
     * })
     */
    private $space;

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
     * @Groups({"api_admin_resident_rent_grid", "api_admin_resident_rent_list", "api_admin_resident_rent_get"})
     */
    private $end;

    /**
     * @var int
     * @Assert\NotBlank(groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"})
     * @Assert\Choice(
     *     callback={"App\Model\ResidentRentType","getTypeValues"},
     *     groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"}
     * )
     * @ORM\Column(name="type", type="integer", length=1)
     * @Groups({"api_admin_resident_rent_grid", "api_admin_resident_rent_list", "api_admin_resident_rent_get"})
     */
    private $type = ResidentRentType::MONTHLY;

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
     * @var string $source
     * @ORM\Column(name="source", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Source cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_rent_add", "api_admin_resident_rent_edit"}
     * )
     * @Groups({"api_admin_resident_rent_grid", "api_admin_resident_rent_list", "api_admin_resident_rent_get"})
     */
    private $source = '[]';

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
     * @return Space|null
     */
    public function getSpace(): ?Space
    {
        return $this->space;
    }

    /**
     * @param Space|null $space
     * @return ResidentRent
     */
    public function setSpace(?Space $space): self
    {
        $this->space = $space;

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
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }
}
