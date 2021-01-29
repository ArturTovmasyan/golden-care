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
 * Class ResidentCreditItem
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentCreditItemRepository")
 * @ORM\Table(name="tbl_resident_credit_item")
 * @UniqueEntity(
 *     fields={"resident", "creditItem"},
 *     errorPath="credit_item_id",
 *     message="The value is already in use for this Resident.",
 *     groups={
 *          "api_admin_resident_credit_item_add",
 *          "api_admin_resident_credit_item_edit"
 *     }
 * )
 * @Grid(
 *     api_admin_resident_credit_item_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rci.id"
 *          },
 *          {
 *              "id"         = "start",
 *              "type"       = "date",
 *              "field"      = "rci.start"
 *          },
 *          {
 *              "id"         = "end",
 *              "type"       = "date",
 *              "field"      = "rci.end"
 *          },
 *          {
 *              "id"         = "credit_item",
 *              "type"       = "string",
 *              "field"      = "ci.title"
 *          },
 *          {
 *              "id"         = "amount",
 *              "type"       = "currency",
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
     *     "api_admin_resident_credit_item_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_resident_credit_item_add",
     *     "api_admin_resident_credit_item_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentCreditItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_credit_item_list",
     *     "api_admin_resident_credit_item_get"
     * })
     */
    private $resident;

    /**
     * @var CreditItem
     * @Assert\NotNull(message = "Please select a Credit Item", groups={
     *     "api_admin_resident_credit_item_add",
     *     "api_admin_resident_credit_item_edit"
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
     * @var DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_credit_item_add",
     *     "api_admin_resident_credit_item_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_resident_credit_item_add",
     *     "api_admin_resident_credit_item_edit"
     * })
     * @ORM\Column(name="start", type="datetime")
     * @Groups({
     *     "api_admin_resident_credit_item_list",
     *     "api_admin_resident_credit_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $start;

    /**
     * @var DateTime
     * @Assert\DateTime(groups={
     *     "api_admin_resident_credit_item_add",
     *     "api_admin_resident_credit_item_edit"
     * })
     * @ORM\Column(name="end", type="datetime", nullable=true)
     * @Groups({
     *     "api_admin_resident_credit_item_list",
     *     "api_admin_resident_credit_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $end;

    /**
     * @var float
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_credit_item_add",
     *     "api_admin_resident_credit_item_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^[1-9][0-9]*$)|(^[0-9]+(\.[0-9]{1,2})$)/",
     *      message="The value entered is not a valid type. Examples of valid entries: '2000, 0.55, 100.34'.",
     *      groups={
     *          "api_admin_resident_credit_item_add",
     *          "api_admin_resident_credit_item_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_credit_item_add",
     *          "api_admin_resident_credit_item_edit"
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
     *          "api_admin_resident_credit_item_edit"
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
     *     "api_admin_resident_credit_item_add",
     *     "api_admin_resident_credit_item_edit"
     * })
     */
    public function areAmountValid(ExecutionContextInterface $context): void
    {
        $creditItemAmount = $this->creditItem !== null && $this->creditItem->getAmount() > 0 ? $this->creditItem->getAmount() : null;

        if ($creditItemAmount !== null && $this->amount > $creditItemAmount) {
            $context->buildViolation('Value should be less or equal to Credit Item amount "' . $creditItemAmount . '".')
                ->atPath('amount')
                ->addViolation();
        }
    }
}