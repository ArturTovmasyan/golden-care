<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentResidentPrivatePayPaymentReceivedItemPaymentReceivedItem
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentPrivatePayPaymentReceivedItemRepository")
 * @ORM\Table(name="tbl_resident_private_pay_payment_received_item")
 * @Grid(
 *     api_admin_resident_private_pay_payment_received_item_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rpri.id"
 *          },
 *          {
 *              "id"         = "payment_type",
 *              "type"       = "string",
 *              "field"      = "pt.title"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "rpri.date"
 *          },
 *          {
 *              "id"         = "amount",
 *              "type"       = "number",
 *              "field"      = "rpri.amount"
 *          },
 *          {
 *              "id"         = "transaction_number",
 *              "type"       = "string",
 *              "field"      = "rpri.transactionNumber"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(rpri.notes, 1, 100)), CASE WHEN LENGTH(rpri.notes) > 100 THEN '…' ELSE '' END)",
 *              "sortable"   = false,
 *              "width"      = "10rem"
 *          }
 *     }
 * )
 */
class ResidentPrivatePayPaymentReceivedItem
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_private_pay_payment_received_item_list",
     *     "api_admin_resident_private_pay_payment_received_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $id;

    /**
     * @var ResidentLedger
     * @Assert\NotNull(message = "Please select a Ledger", groups={
     *     "api_admin_resident_private_pay_payment_received_item_add",
     *     "api_admin_resident_private_pay_payment_received_item_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\ResidentLedger", inversedBy="residentPrivatePayPaymentReceivedItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ledger", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_private_pay_payment_received_item_list",
     *     "api_admin_resident_private_pay_payment_received_item_get"
     * })
     */
    private $ledger;

    /**
     * @var RpPaymentType
     * @Assert\NotNull(message = "Please select a Payment Type", groups={
     *     "api_admin_resident_private_pay_payment_received_item_add",
     *     "api_admin_resident_private_pay_payment_received_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\RpPaymentType", inversedBy="residentPrivatePayPaymentReceivedItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_payment_type", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_private_pay_payment_received_item_list",
     *     "api_admin_resident_private_pay_payment_received_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $paymentType;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_private_pay_payment_received_item_add",
     *     "api_admin_resident_private_pay_payment_received_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_resident_private_pay_payment_received_item_add",
     *     "api_admin_resident_private_pay_payment_received_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_admin_resident_private_pay_payment_received_item_list",
     *     "api_admin_resident_private_pay_payment_received_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $date;

    /**
     * @var float
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_private_pay_payment_received_item_add",
     *     "api_admin_resident_private_pay_payment_received_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^[1-9][0-9]*$)|(^[0-9]+(\.[0-9]{1,2})$)/",
     *      message="The value entered is not a valid type. Examples of valid entries: '2000, 0.55, 100.34'.",
     *      groups={
     *          "api_admin_resident_private_pay_payment_received_item_add",
     *          "api_admin_resident_private_pay_payment_received_item_edit",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_private_pay_payment_received_item_add",
     *          "api_admin_resident_private_pay_payment_received_item_edit",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_private_pay_payment_received_item_list",
     *     "api_admin_resident_private_pay_payment_received_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $amount;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_private_pay_payment_received_item_add",
     *     "api_admin_resident_private_pay_payment_received_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\Length(
     *      max = 32,
     *      maxMessage = "Check/Transaction # cannot be longer than {{ limit }} characters",
     *      groups={
     *           "api_admin_resident_private_pay_payment_received_item_add",
     *           "api_admin_resident_private_pay_payment_received_item_edit",
     *           "api_admin_resident_ledger_edit"
     * })
     * @ORM\Column(name="transaction_number", type="string", length=32)
     * @Groups({
     *     "api_admin_resident_private_pay_payment_received_item_list",
     *     "api_admin_resident_private_pay_payment_received_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $transactionNumber;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_private_pay_payment_received_item_add",
     *          "api_admin_resident_private_pay_payment_received_item_edit",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_private_pay_payment_received_item_list",
     *     "api_admin_resident_private_pay_payment_received_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $notes;

    /**
     * @var ResidentResponsiblePerson
     * @Assert\NotNull(message = "Please select a Responsible Person", groups={
     *     "api_admin_resident_private_pay_payment_received_item_add",
     *     "api_admin_resident_private_pay_payment_received_item_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\ResidentResponsiblePerson", inversedBy="residentPrivatePayPaymentReceivedItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_responsible_person", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_admin_resident_private_pay_payment_received_item_list",
     *     "api_admin_resident_private_pay_payment_received_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $responsiblePerson;

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
     * @return RpPaymentType|null
     */
    public function getPaymentType(): ?RpPaymentType
    {
        return $this->paymentType;
    }

    /**
     * @param RpPaymentType|null $paymentType
     */
    public function setPaymentType(?RpPaymentType $paymentType): void
    {
        $this->paymentType = $paymentType;
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
     * @return string|null
     */
    public function getTransactionNumber(): ?string
    {
        return $this->transactionNumber;
    }

    /**
     * @param string|null $transactionNumber
     */
    public function setTransactionNumber(?string $transactionNumber): void
    {
        $this->transactionNumber = $transactionNumber;
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
     * @return ResidentResponsiblePerson|null
     */
    public function getResponsiblePerson(): ?ResidentResponsiblePerson
    {
        return $this->responsiblePerson;
    }

    /**
     * @param ResidentResponsiblePerson|null $responsiblePerson
     */
    public function setResponsiblePerson(?ResidentResponsiblePerson $responsiblePerson): void
    {
        $this->responsiblePerson = $responsiblePerson;
    }
}
