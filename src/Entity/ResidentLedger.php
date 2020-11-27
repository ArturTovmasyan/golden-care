<?php

namespace App\Entity;

use App\Api\V1\Common\Service\PreviousAndNextItemsService;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use App\Annotation\Grid;

/**
 * Class ResidentLedger
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentLedgerRepository")
 * @ORM\Table(name="tbl_resident_ledger")
 * @Grid(
 *     api_admin_resident_ledger_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rl.id"
 *          },
 *          {
 *              "id"         = "date_created",
 *              "type"       = "date",
 *              "field"      = "rl.createdAt",
 *              "link"       = "/resident/ledger/:id"
 *          },
 *          {
 *              "id"         = "private_pay_balance_due",
 *              "type"       = "number",
 *              "field"      = "rl.privatePayBalanceDue"
 *          },
 *          {
 *              "id"         = "not_private_pay_balance_due",
 *              "type"       = "number",
 *              "field"      = "rl.notPrivatePayBalanceDue"
 *          },
 *          {
 *              "id"         = "info",
 *              "sortable"   = false,
 *              "type"       = "json",
 *              "field"      = "info"
 *          }
 *     }
 * )
 */
class ResidentLedger implements PreviousAndNextItemsService
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get",
     *     "api_admin_resident_expense_item_list",
     *     "api_admin_resident_expense_item_get",
     *     "api_admin_resident_credit_item_list",
     *     "api_admin_resident_credit_item_get",
     *     "api_admin_resident_discount_item_list",
     *     "api_admin_resident_discount_item_get",
     *     "api_admin_resident_payment_received_item_list",
     *     "api_admin_resident_payment_received_item_get",
     *     "api_admin_resident_away_days_list",
     *     "api_admin_resident_away_days_get",
     *     "api_admin_resident_key_finance_date_list",
     *     "api_admin_resident_key_finance_date_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_resident_ledger_add",
     *     "api_admin_resident_ledger_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentLedgers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $resident;

    /**
     * @var float
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_ledger_add",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_ledger_add",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $amount = 0;

    /**
     * @var float
     * @ORM\Column(name="balance_due", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_ledger_add",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Balance Due cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_ledger_add",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $balanceDue = 0;

    /**
     * @var float
     * @ORM\Column(name="private_pay_balance_due", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_ledger_add",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Private Pay Balance Due cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_ledger_add",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $privatePayBalanceDue = 0;

    /**
     * @var float
     * @ORM\Column(name="not_private_pay_balance_due", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_ledger_add",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Not Private Pay Balance Due cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_ledger_add",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $notPrivatePayBalanceDue = 0;

    /**
     * @var float
     * @ORM\Column(name="prior_private_pay_balance_due", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_ledger_add",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Prior Private Pay Balance Due cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_ledger_add",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $priorPrivatePayBalanceDue = 0;

    /**
     * @var float
     * @ORM\Column(name="prior_not_private_pay_balance_due", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_ledger_add",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Prior Not Private Pay Balance Due cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_ledger_add",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $priorNotPrivatePayBalanceDue = 0;

    /**
     * @var array $source
     * @ORM\Column(name="source", type="json_array", nullable=true)
     * @Assert\Count(
     *      max = 10,
     *      maxMessage = "You cannot enter more than {{ limit }} sources",
     *      groups={
     *          "api_admin_resident_ledger_add",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $source = [];

    /**
     * @var array $privatPaySource
     * @ORM\Column(name="privat_pay_source", type="json_array", nullable=true)
     * @Assert\Count(
     *      max = 10,
     *      maxMessage = "You cannot enter more than {{ limit }} sources",
     *      groups={
     *          "api_admin_resident_ledger_add",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $privatPaySource = [];

    /**
     * @var array $notPrivatPaySource
     * @ORM\Column(name="not_privat_pay_source", type="json_array", nullable=true)
     * @Assert\Count(
     *      max = 10,
     *      maxMessage = "You cannot enter more than {{ limit }} sources",
     *      groups={
     *          "api_admin_resident_ledger_add",
     *          "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $notPrivatPaySource = [];

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentExpenseItem", mappedBy="ledger", cascade={"remove", "persist"})
     * @ORM\OrderBy({"date" = "ASC"})
     * @Assert\Valid(groups={
     *     "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $residentExpenseItems;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentCreditItem", mappedBy="ledger", cascade={"remove", "persist"})
     * @ORM\OrderBy({"date" = "ASC"})
     * @Assert\Valid(groups={
     *     "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $residentCreditItems;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentDiscountItem", mappedBy="ledger", cascade={"remove", "persist"})
     * @ORM\OrderBy({"date" = "ASC"})
     * @Assert\Valid(groups={
     *     "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $residentDiscountItems;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentPaymentReceivedItem", mappedBy="ledger", cascade={"remove", "persist"})
     * @ORM\OrderBy({"date" = "ASC"})
     * @Assert\Valid(groups={
     *     "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $residentPaymentReceivedItems;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentAwayDays", mappedBy="ledger", cascade={"remove", "persist"})
     * @ORM\OrderBy({"start" = "ASC"})
     * @Assert\Valid(groups={
     *     "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $residentAwayDays;

    /**
     * @var LatePayment
     * @ORM\ManyToOne(targetEntity="App\Entity\LatePayment", inversedBy="residentLedgers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_late_payment", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $latePayment;

    /**
     * @var int
     */
    private $previousId;

    /**
     * @var int
     */
    private $nextId;

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("previous_ledger_id")
     * @Serializer\Groups({
     *     "api_admin_resident_ledger_get"
     * })
     * @return int|null
     */
    public function getPreviousLedgerId(): ?int
    {
        return $this->previousId;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("next_ledger_id")
     * @Serializer\Groups({
     *     "api_admin_resident_ledger_get"
     * })
     * @return int|null
     */
    public function getNextLedgerId(): ?int
    {
        return $this->nextId;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("date_created")
     * @Serializer\Groups({
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    public function getDateCreated(): ?\DateTime
    {
        return $this->getCreatedAt();
    }

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
     * @return float|null
     */
    public function getBalanceDue(): ?float
    {
        return $this->balanceDue;
    }

    /**
     * @param float|null $balanceDue
     */
    public function setBalanceDue(?float $balanceDue): void
    {
        $this->balanceDue = $balanceDue;
    }

    /**
     * @return float|null
     */
    public function getPrivatePayBalanceDue(): ?float
    {
        return $this->privatePayBalanceDue;
    }

    /**
     * @param float|null $privatePayBalanceDue
     */
    public function setPrivatePayBalanceDue(?float $privatePayBalanceDue): void
    {
        $this->privatePayBalanceDue = $privatePayBalanceDue;
    }

    /**
     * @return float|null
     */
    public function getNotPrivatePayBalanceDue(): ?float
    {
        return $this->notPrivatePayBalanceDue;
    }

    /**
     * @param float|null $notPrivatePayBalanceDue
     */
    public function setNotPrivatePayBalanceDue(?float $notPrivatePayBalanceDue): void
    {
        $this->notPrivatePayBalanceDue = $notPrivatePayBalanceDue;
    }

    /**
     * @return float|null
     */
    public function getPriorPrivatePayBalanceDue(): ?float
    {
        return $this->priorPrivatePayBalanceDue;
    }

    /**
     * @param float|null $priorPrivatePayBalanceDue
     */
    public function setPriorPrivatePayBalanceDue(?float $priorPrivatePayBalanceDue): void
    {
        $this->priorPrivatePayBalanceDue = $priorPrivatePayBalanceDue;
    }

    /**
     * @return float|null
     */
    public function getPriorNotPrivatePayBalanceDue(): ?float
    {
        return $this->priorNotPrivatePayBalanceDue;
    }

    /**
     * @param float|null $priorNotPrivatePayBalanceDue
     */
    public function setPriorNotPrivatePayBalanceDue(?float $priorNotPrivatePayBalanceDue): void
    {
        $this->priorNotPrivatePayBalanceDue = $priorNotPrivatePayBalanceDue;
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
     * @return array
     */
    public function getPrivatPaySource(): array
    {
        return $this->privatPaySource;
    }

    /**
     * @param array $privatPaySource
     */
    public function setPrivatPaySource(array $privatPaySource): void
    {
        $this->privatPaySource = $privatPaySource;
    }

    /**
     * @return array
     */
    public function getNotPrivatPaySource(): array
    {
        return $this->notPrivatPaySource;
    }

    /**
     * @param array $notPrivatPaySource
     */
    public function setNotPrivatPaySource(array $notPrivatPaySource): void
    {
        $this->notPrivatPaySource = $notPrivatPaySource;
    }

    /**
     * @return mixed
     */
    public function getResidentExpenseItems()
    {
        return $this->residentExpenseItems;
    }

    /**
     * @param mixed $residentExpenseItems
     */
    public function setResidentExpenseItems($residentExpenseItems): void
    {
        $this->residentExpenseItems = $residentExpenseItems;
    }

    /**
     * @param ResidentExpenseItem $residentExpenseItem
     */
    public function addResidentExpenseItem($residentExpenseItem): void
    {
        $residentExpenseItem->setLedger($this);
        $this->residentExpenseItems->add($residentExpenseItem);
    }

    /**
     * @param ResidentExpenseItem $residentExpenseItem
     */
    public function removeResidentExpenseItem($residentExpenseItem): void
    {
        $this->residentExpenseItems->removeElement($residentExpenseItem);
    }

    /**
     * @return mixed
     */
    public function getResidentCreditItems()
    {
        return $this->residentCreditItems;
    }

    /**
     * @param ArrayCollection $residentCreditItems
     */
    public function setResidentCreditItems(ArrayCollection $residentCreditItems): void
    {
        $this->residentCreditItems = $residentCreditItems;
    }

    /**
     * @param ResidentCreditItem $residentCreditItem
     */
    public function addResidentCreditItem($residentCreditItem): void
    {
        $residentCreditItem->setLedger($this);
        $this->residentCreditItems->add($residentCreditItem);
    }

    /**
     * @param ResidentCreditItem $residentCreditItem
     */
    public function removeResidentCreditItem($residentCreditItem): void
    {
        $this->residentCreditItems->removeElement($residentCreditItem);
    }

    /**
     * @return mixed
     */
    public function getResidentDiscountItems()
    {
        return $this->residentDiscountItems;
    }

    /**
     * @param ArrayCollection $residentDiscountItems
     */
    public function setResidentDiscountItems(ArrayCollection $residentDiscountItems): void
    {
        $this->residentDiscountItems = $residentDiscountItems;
    }

    /**
     * @param ResidentDiscountItem $residentDiscountItem
     */
    public function addResidentDiscountItem($residentDiscountItem): void
    {
        $residentDiscountItem->setLedger($this);
        $this->residentDiscountItems->add($residentDiscountItem);
    }

    /**
     * @param ResidentDiscountItem $residentDiscountItem
     */
    public function removeResidentDiscountItem($residentDiscountItem): void
    {
        $this->residentDiscountItems->removeElement($residentDiscountItem);
    }

    /**
     * @return mixed
     */
    public function getResidentPaymentReceivedItems()
    {
        return $this->residentPaymentReceivedItems;
    }

    /**
     * @param ArrayCollection $residentPaymentReceivedItems
     */
    public function setResidentPaymentReceivedItems(ArrayCollection $residentPaymentReceivedItems): void
    {
        $this->residentPaymentReceivedItems = $residentPaymentReceivedItems;
    }

    /**
     * @param ResidentPaymentReceivedItem $residentPaymentReceivedItem
     */
    public function addResidentPaymentReceivedItem($residentPaymentReceivedItem): void
    {
        $residentPaymentReceivedItem->setLedger($this);
        $this->residentPaymentReceivedItems->add($residentPaymentReceivedItem);
    }

    /**
     * @param ResidentPaymentReceivedItem $residentPaymentReceivedItem
     */
    public function removeResidentPaymentReceivedItem($residentPaymentReceivedItem): void
    {
        $this->residentPaymentReceivedItems->removeElement($residentPaymentReceivedItem);
    }

    /**
     * @return mixed
     */
    public function getResidentAwayDays()
    {
        return $this->residentAwayDays;
    }

    /**
     * @param ArrayCollection $residentAwayDays
     */
    public function setResidentAwayDays(ArrayCollection $residentAwayDays): void
    {
        $this->residentAwayDays = $residentAwayDays;
    }

    /**
     * @param ResidentAwayDays $residentAwayDay
     */
    public function addResidentAwayDays($residentAwayDay): void
    {
        $residentAwayDay->setLedger($this);
        $this->residentAwayDays->add($residentAwayDay);
    }

    /**
     * @param ResidentAwayDays $residentAwayDay
     */
    public function removeResidentAwayDays($residentAwayDay): void
    {
        $this->residentAwayDays->removeElement($residentAwayDay);
    }

    /**
     * @return LatePayment|null
     */
    public function getLatePayment(): ?LatePayment
    {
        return $this->latePayment;
    }

    /**
     * @param LatePayment|null $latePayment
     */
    public function setLatePayment(?LatePayment $latePayment): void
    {
        $this->latePayment = $latePayment;
    }

    /**
     * @return int|null
     */
    public function getPreviousId(): ?int
    {
        return $this->previousId;
    }

    /**
     * @param int|null $previousId
     */
    public function setPreviousId(?int $previousId): void
    {
        $this->previousId = $previousId;
    }

    /**
     * @return int|null
     */
    public function getNextId(): ?int
    {
        return $this->nextId;
    }

    /**
     * @param int|null $nextId
     */
    public function setNextId(?int $nextId): void
    {
        $this->nextId = $nextId;
    }
}
