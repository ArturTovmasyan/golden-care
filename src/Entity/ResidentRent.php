<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
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
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rr.id"
 *          },
 *          {
 *              "id"         = "start",
 *              "type"       = "date",
 *              "field"      = "rr.start",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "end",
 *              "type"       = "date",
 *              "field"      = "rr.end",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "amount",
 *              "type"       = "number",
 *              "field"      = "rr.amount"
 *          },
 *          {
 *              "id"         = "reason",
 *              "type"       = "string",
 *              "field"      = "rrn.title"
 *          },
 *          {
 *              "id"         = "info",
 *              "sortable"   = false,
 *              "type"       = "json",
 *              "field"      = "info"
 *          },
 *          {
 *              "id"         = "notes",
 *              "sortable"   = false,
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(rr.notes, 1, 100)), CASE WHEN LENGTH(rr.notes) > 100 THEN '…' ELSE '' END)"
 *          }
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
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_resident_rent_add",
     *     "api_admin_resident_rent_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentRents")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get"
     * })
     */
    private $resident;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_rent_add",
     *     "api_admin_resident_rent_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_resident_rent_add",
     *     "api_admin_resident_rent_edit"
     * })
     * @ORM\Column(name="start", type="datetime")
     * @Groups({
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get"
     * })
     */
    private $start;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *     "api_admin_resident_rent_add",
     *     "api_admin_resident_rent_edit"
     * })
     * @ORM\Column(name="end", type="datetime", nullable=true)
     * @Groups({
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get"
     * })
     */
    private $end;

    /**
     * @var float
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_rent_add",
     *     "api_admin_resident_rent_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^0$)|(^[1-9][0-9]*$)|(^[0-9]+(\.[0-9]{1,2})$)/",
     *      message="The value entered is not a valid type. Examples of valid entries: '2000, 0.55, 100.34'.",
     *      groups={
     *          "api_admin_resident_rent_add",
     *          "api_admin_resident_rent_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_rent_add",
     *          "api_admin_resident_rent_edit"
     * })
     * @Groups({
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $amount;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_rent_add",
     *          "api_admin_resident_rent_edit"
     * })
     * @Groups({
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get"
     * })
     */
    private $notes;

    /**
     * @var array $source
     * @ORM\Column(name="source", type="json_array", nullable=true)
     * @Assert\Count(
     *      max = 10,
     *      maxMessage = "You cannot enter more than {{ limit }} sources",
     *      groups={
     *          "api_admin_resident_rent_add",
     *          "api_admin_resident_rent_edit"
     * })
     * @Groups({
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get"
     * })
     */
    private $source = [];

    /**
     * @var RentReason
     * @ORM\ManyToOne(targetEntity="App\Entity\RentReason", inversedBy="residentRents")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_rent_reason", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get"
     * })
     */
    private $reason;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentPrivatePayPaymentReceivedItem", mappedBy="rent", cascade={"remove", "persist"})
     */
    private $residentPrivatePayPaymentReceivedItems;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentNotPrivatePayPaymentReceivedItem", mappedBy="rent", cascade={"remove", "persist"})
     */
    private $residentNotPrivatePayPaymentReceivedItems;

    /**
     * @return int
     */
    public function getId(): ?int
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
     */
    public function setResident(?Resident $resident): void
    {
        $this->resident = $resident;
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

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param float|null $amount
     */
    public function setAmount(?float $amount): void
    {
        $this->amount = $amount;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
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

    /**
     * @return RentReason|null
     */
    public function getReason(): ?RentReason
    {
        return $this->reason;
    }

    /**
     * @param RentReason|null $reason
     */
    public function setReason(?RentReason $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentPrivatePayPaymentReceivedItems(): ArrayCollection
    {
        return $this->residentPrivatePayPaymentReceivedItems;
    }

    /**
     * @param ArrayCollection $residentPrivatePayPaymentReceivedItems
     */
    public function setResidentPrivatePayPaymentReceivedItems(ArrayCollection $residentPrivatePayPaymentReceivedItems): void
    {
        $this->residentPrivatePayPaymentReceivedItems = $residentPrivatePayPaymentReceivedItems;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentNotPrivatePayPaymentReceivedItems(): ArrayCollection
    {
        return $this->residentNotPrivatePayPaymentReceivedItems;
    }

    /**
     * @param ArrayCollection $residentNotPrivatePayPaymentReceivedItems
     */
    public function setResidentNotPrivatePayPaymentReceivedItems(ArrayCollection $residentNotPrivatePayPaymentReceivedItems): void
    {
        $this->residentNotPrivatePayPaymentReceivedItems = $residentNotPrivatePayPaymentReceivedItems;
    }
}
