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
 * Class ResidentCreditItem
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentCreditItemRepository")
 * @ORM\Table(name="tbl_resident_credit_item")
 * @Grid(
 *     api_admin_resident_credit_item_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rci.id"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "rci.date"
 *          },
 *          {
 *              "id"         = "credit_item",
 *              "type"       = "string",
 *              "field"      = "ci.title"
 *          },
 *          {
 *              "id"         = "amount",
 *              "type"       = "number",
 *              "field"      = "rci.amount"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(rci.notes, 1, 100)), CASE WHEN LENGTH(rci.notes) > 100 THEN '…' ELSE '' END)",
 *              "sortable"   = false,
 *              "width"      = "10rem"
 *          }
 *     }
 * )
 */
class ResidentCreditItem
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_credit_item_list",
     *     "api_admin_resident_credit_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $id;

    /**
     * @var ResidentLedger
     * @Assert\NotNull(message = "Please select a Ledger", groups={
     *     "api_admin_resident_credit_item_add",
     *     "api_admin_resident_credit_item_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\ResidentLedger", inversedBy="residentCreditItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ledger", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_credit_item_list",
     *     "api_admin_resident_credit_item_get"
     * })
     */
    private $ledger;

    /**
     * @var CreditItem
     * @Assert\NotNull(message = "Please select a Credit Item", groups={
     *     "api_admin_resident_credit_item_add",
     *     "api_admin_resident_credit_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\CreditItem", inversedBy="residentCreditItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_credit_item", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_credit_item_list",
     *     "api_admin_resident_credit_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $creditItem;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_credit_item_add",
     *     "api_admin_resident_credit_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_resident_credit_item_add",
     *     "api_admin_resident_credit_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_admin_resident_credit_item_list",
     *     "api_admin_resident_credit_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $date;

    /**
     * @var float
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_credit_item_add",
     *     "api_admin_resident_credit_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^[1-9][0-9]*$)|(^[0-9]+(\.[0-9]{1,2})$)/",
     *      message="The value entered is not a valid type. Examples of valid entries: '2000, 0.55, 100.34'.",
     *      groups={
     *          "api_admin_resident_credit_item_add",
     *          "api_admin_resident_credit_item_edit",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_credit_item_add",
     *          "api_admin_resident_credit_item_edit",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_credit_item_list",
     *     "api_admin_resident_credit_item_get",
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
     *          "api_admin_resident_credit_item_add",
     *          "api_admin_resident_credit_item_edit",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_credit_item_list",
     *     "api_admin_resident_credit_item_get",
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
     * @return CreditItem|null
     */
    public function getCreditItem(): ?CreditItem
    {
        return $this->creditItem;
    }

    /**
     * @param CreditItem|null $creditItem
     */
    public function setCreditItem(?CreditItem $creditItem): void
    {
        $this->creditItem = $creditItem;
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
     *     "api_admin_resident_credit_item_add",
     *     "api_admin_resident_credit_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     */
    public function areAmountValid(ExecutionContextInterface $context): void
    {
        $creditItemAmount = $this->creditItem !== null && $this->creditItem->getAmount() > 0 ? $this->creditItem->getAmount() : 0;

        if ($this->amount > $creditItemAmount) {
            $context->buildViolation('The amount "' . $this->amount . '" should be less or equal to Credit Item amount "' . $creditItemAmount . '".')
                ->atPath('amount')
                ->addViolation();
        }
    }
}
