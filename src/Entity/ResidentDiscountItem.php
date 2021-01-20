<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class ResidentDiscountItem
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentDiscountItemRepository")
 * @ORM\Table(name="tbl_resident_discount_item")
 * @UniqueEntity(
 *     fields={"resident", "discountItem"},
 *     errorPath="discount_item_id",
 *     message="The value is already in use for this Resident.",
 *     groups={
 *          "api_admin_resident_discount_item_add",
 *          "api_admin_resident_discount_item_edit"
 *     }
 * )
 * @Grid(
 *     api_admin_resident_discount_item_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rdi.id"
 *          },
 *          {
 *              "id"         = "start",
 *              "type"       = "date",
 *              "field"      = "rdi.start"
 *          },
 *          {
 *              "id"         = "end",
 *              "type"       = "date",
 *              "field"      = "rdi.end"
 *          },
 *          {
 *              "id"         = "discount_item",
 *              "type"       = "string",
 *              "field"      = "di.title"
 *          },
 *          {
 *              "id"         = "amount",
 *              "type"       = "number",
 *              "field"      = "rdi.amount"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(rdi.notes, 1, 100)), CASE WHEN LENGTH(rdi.notes) > 100 THEN '…' ELSE '' END)",
 *              "sortable"   = false,
 *              "width"      = "10rem"
 *          }
 *     }
 * )
 */
class ResidentDiscountItem
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_discount_item_list",
     *     "api_admin_resident_discount_item_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_resident_discount_item_add",
     *     "api_admin_resident_discount_item_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentDiscountItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_discount_item_list",
     *     "api_admin_resident_discount_item_get"
     * })
     */
    private $resident;

    /**
     * @var DiscountItem
     * @Assert\NotNull(message = "Please select a Discount Item", groups={
     *     "api_admin_resident_discount_item_add",
     *     "api_admin_resident_discount_item_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\DiscountItem", inversedBy="residentDiscountItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_discount_item", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_discount_item_list",
     *     "api_admin_resident_discount_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $discountItem;

    /**
     * @var DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_discount_item_add",
     *     "api_admin_resident_discount_item_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_resident_discount_item_add",
     *     "api_admin_resident_discount_item_edit"
     * })
     * @ORM\Column(name="start", type="datetime")
     * @Groups({
     *     "api_admin_resident_discount_item_list",
     *     "api_admin_resident_discount_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $start;

    /**
     * @var DateTime
     * @Assert\DateTime(groups={
     *     "api_admin_resident_discount_item_add",
     *     "api_admin_resident_discount_item_edit"
     * })
     * @ORM\Column(name="end", type="datetime", nullable=true)
     * @Groups({
     *     "api_admin_resident_discount_item_list",
     *     "api_admin_resident_discount_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $end;

    /**
     * @var float
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_discount_item_add",
     *     "api_admin_resident_discount_item_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^[1-9][0-9]*$)|(^[0-9]+(\.[0-9]{1,2})$)/",
     *      message="The value entered is not a valid type. Examples of valid entries: '2000, 0.55, 100.34'.",
     *      groups={
     *          "api_admin_resident_discount_item_add",
     *          "api_admin_resident_discount_item_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_discount_item_add",
     *          "api_admin_resident_discount_item_edit"
     * })
     * @Groups({
     *     "api_admin_resident_discount_item_list",
     *     "api_admin_resident_discount_item_get",
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
     *          "api_admin_resident_discount_item_add",
     *          "api_admin_resident_discount_item_edit"
     * })
     * @Groups({
     *     "api_admin_resident_discount_item_list",
     *     "api_admin_resident_discount_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $notes;

    /**
     * @return int|null
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
     * @return DiscountItem|null
     */
    public function getDiscountItem(): ?DiscountItem
    {
        return $this->discountItem;
    }

    /**
     * @param DiscountItem|null $discountItem
     */
    public function setDiscountItem(?DiscountItem $discountItem): void
    {
        $this->discountItem = $discountItem;
    }

    /**
     * @return DateTime|null
     */
    public function getStart(): ?DateTime
    {
        return $this->start;
    }

    /**
     * @param DateTime|null $start
     */
    public function setStart(?DateTime $start): void
    {
        $this->start = $start;
    }

    /**
     * @return DateTime|null
     */
    public function getEnd(): ?DateTime
    {
        return $this->end;
    }

    /**
     * @param DateTime|null $end
     */
    public function setEnd(?DateTime $end): void
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

    /**
     * @return null|string
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param null|string $notes
     */
    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback(groups={
     *     "api_admin_resident_discount_item_add",
     *     "api_admin_resident_discount_item_edit"
     * })
     */
    public function areAmountValid(ExecutionContextInterface $context): void
    {
        $discountItemAmount = $this->discountItem !== null && $this->discountItem->getAmount() > 0 ? $this->discountItem->getAmount() : null;

        if ($discountItemAmount !== null && $this->amount > $discountItemAmount) {
            $context->buildViolation('Value should be less or equal to Discount Item amount "' . $discountItemAmount . '".')
                ->atPath('amount')
                ->addViolation();
        }
    }
}
