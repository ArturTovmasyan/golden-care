<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class ResidentDiscountItem
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentDiscountItemRepository")
 * @ORM\Table(name="tbl_resident_discount_item")
 * @Grid(
 *     api_admin_resident_discount_item_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rdi.id"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "rdi.date"
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
     *     "api_admin_resident_discount_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $id;

    /**
     * @var ResidentLedger
     * @Assert\NotNull(message = "Please select a Ledger", groups={
     *     "api_admin_resident_discount_item_add",
     *     "api_admin_resident_discount_item_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\ResidentLedger", inversedBy="residentDiscountItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ledger", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_discount_item_list",
     *     "api_admin_resident_discount_item_get"
     * })
     */
    private $ledger;

    /**
     * @var DiscountItem
     * @Assert\NotNull(message = "Please select a Discount Item", groups={
     *     "api_admin_resident_discount_item_add",
     *     "api_admin_resident_discount_item_edit",
     *     "api_admin_resident_ledger_edit"
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
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_discount_item_add",
     *     "api_admin_resident_discount_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_resident_discount_item_add",
     *     "api_admin_resident_discount_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_admin_resident_discount_item_list",
     *     "api_admin_resident_discount_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $date;

    /**
     * @var float
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_discount_item_add",
     *     "api_admin_resident_discount_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^[1-9][0-9]*$)|(^[0-9]+(\.[0-9]{1,2})$)/",
     *      message="The value entered is not a valid type. Examples of valid entries: '2000, 0.55, 100.34'.",
     *      groups={
     *          "api_admin_resident_discount_item_add",
     *          "api_admin_resident_discount_item_edit",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_discount_item_add",
     *          "api_admin_resident_discount_item_edit",
     *          "api_admin_resident_ledger_edit"
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
     *          "api_admin_resident_discount_item_edit",
     *          "api_admin_resident_ledger_edit"
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
     * @return ResidentLedger|null
     */
    public function getLedger(): ?ResidentLedger
    {
        return $this->ledger;
    }

    /**
     * @param ResidentLedger|null $ledger
     */
    public function setLedger(?ResidentLedger $ledger): void
    {
        $this->ledger = $ledger;
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
     * @return \DateTime|null
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime|null $date
     */
    public function setDate(?\DateTime $date): void
    {
        $this->date = $date;
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
     *     "api_admin_resident_discount_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     */
    public function areAmountValid(ExecutionContextInterface $context): void
    {
        $discountItemAmount = $this->discountItem !== null && $this->discountItem->getAmount() > 0 ? $this->discountItem->getAmount() : 0;

        if ($this->amount > $discountItemAmount) {
            $context->buildViolation('Value should be less or equal to Discount Item amount "' . $discountItemAmount . '".')
                ->atPath('amount')
                ->addViolation();
        }
    }
}
