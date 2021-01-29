<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentExpenseItem
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentExpenseItemRepository")
 * @ORM\Table(name="tbl_resident_expense_item")
 * @Grid(
 *     api_admin_resident_expense_item_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rei.id"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "rei.date"
 *          },
 *          {
 *              "id"         = "expense_item",
 *              "type"       = "string",
 *              "field"      = "ei.title"
 *          },
 *          {
 *              "id"         = "amount",
 *              "type"       = "currency",
 *              "field"      = "rei.amount"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(rei.notes, 1, 100)), CASE WHEN LENGTH(rei.notes) > 100 THEN '…' ELSE '' END)",
 *              "sortable"   = false,
 *              "width"      = "10rem"
 *          }
 *     }
 * )
 */
class ResidentExpenseItem
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_expense_item_list",
     *     "api_admin_resident_expense_item_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_resident_expense_item_add",
     *     "api_admin_resident_expense_item_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentExpenseItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_expense_item_list",
     *     "api_admin_resident_expense_item_get"
     * })
     */
    private $resident;

    /**
     * @var ExpenseItem
     * @Assert\NotNull(message = "Please select an Expense Item", groups={
     *     "api_admin_resident_expense_item_add",
     *     "api_admin_resident_expense_item_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\ExpenseItem", inversedBy="residentExpenseItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_expense_item", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_expense_item_list",
     *     "api_admin_resident_expense_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $expenseItem;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_expense_item_add",
     *     "api_admin_resident_expense_item_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_resident_expense_item_add",
     *     "api_admin_resident_expense_item_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_admin_resident_expense_item_list",
     *     "api_admin_resident_expense_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $date;

    /**
     * @var float
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_expense_item_add",
     *     "api_admin_resident_expense_item_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^[1-9][0-9]*$)|(^[0-9]+(\.[0-9]{1,2})$)/",
     *      message="The value entered is not a valid type. Examples of valid entries: '2000, 0.55, 100.34'.",
     *      groups={
     *          "api_admin_resident_expense_item_add",
     *          "api_admin_resident_expense_item_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_expense_item_add",
     *          "api_admin_resident_expense_item_edit"
     * })
     * @Groups({
     *     "api_admin_resident_expense_item_list",
     *     "api_admin_resident_expense_item_get",
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
     *          "api_admin_resident_expense_item_add",
     *          "api_admin_resident_expense_item_edit"
     * })
     * @Groups({
     *     "api_admin_resident_expense_item_list",
     *     "api_admin_resident_expense_item_get",
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
     * @return ExpenseItem|null
     */
    public function getExpenseItem(): ?ExpenseItem
    {
        return $this->expenseItem;
    }

    /**
     * @param ExpenseItem|null $expenseItem
     */
    public function setExpenseItem(?ExpenseItem $expenseItem): void
    {
        $this->expenseItem = $expenseItem;
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
}
