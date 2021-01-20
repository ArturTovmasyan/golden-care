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
 *              "type"       = "date_month_year",
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
     *     "api_admin_resident_private_pay_payment_received_item_list",
     *     "api_admin_resident_private_pay_payment_received_item_get",
     *     "api_admin_resident_not_private_pay_payment_received_item_list",
     *     "api_admin_resident_not_private_pay_payment_received_item_get",
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
     * @var array $awayDays
     * @ORM\Column(name="away_days", type="json_array", nullable=true)
     * @Groups({
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $awayDays = [];

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentPrivatePayPaymentReceivedItem", mappedBy="ledger", cascade={"remove", "persist"})
     * @ORM\OrderBy({"date" = "ASC"})
     * @Assert\Valid(groups={
     *     "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $residentPrivatePayPaymentReceivedItems;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentNotPrivatePayPaymentReceivedItem", mappedBy="ledger", cascade={"remove", "persist"})
     * @ORM\OrderBy({"date" = "ASC"})
     * @Assert\Valid(groups={
     *     "api_admin_resident_ledger_edit"
     * })
     * @Groups({
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $residentNotPrivatePayPaymentReceivedItems;

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
     * @var float
     */
    private $priorPrivatePayBalanceDue;

    /**
     * @var float
     */
    private $priorNotPrivatePayBalanceDue;

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("prior_private_pay_balance_due")
     * @Serializer\Groups({
     *     "api_admin_resident_ledger_get"
     * })
     * @return float|null
     */
    public function getLedgerPriorPrivatePayBalanceDue(): ?float
    {
        return $this->priorPrivatePayBalanceDue;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("prior_not_private_pay_balance_due")
     * @Serializer\Groups({
     *     "api_admin_resident_ledger_get"
     * })
     * @return float|null
     */
    public function getLedgerPriorNotPrivatePayBalanceDue(): ?float
    {
        return $this->priorNotPrivatePayBalanceDue;
    }

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
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("resident_expense_items")
     * @Serializer\Groups({
     *     "api_admin_resident_ledger_get"
     * })
     * @return mixed
     */
    public function getExpenseItems()
    {
        $createdAt = $this->getCreatedAt();
        $residentExpenseItems = $this->resident->getResidentExpenseItems();

        $filteredExpenseItems = [];
        if (!empty($residentExpenseItems)) {
            /** @var ResidentExpenseItem $residentExpenseItem */
            foreach ($residentExpenseItems as $residentExpenseItem) {
                $date = $residentExpenseItem->getDate();

                if ($date !== null && $createdAt !== null && $date->format('Y') === $createdAt->format('Y') && $date->format('m') === $createdAt->format('m')) {
                    $filteredExpenseItems[] = $residentExpenseItem;
                }
            }
        }

        return $filteredExpenseItems;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("resident_credit_items")
     * @Serializer\Groups({
     *     "api_admin_resident_ledger_get"
     * })
     * @return mixed
     */
    public function getCreditItems()
    {
        $createdAt = $this->getCreatedAt();
        $residentCreditItems = $this->resident->getResidentCreditItems();

        $filteredCreditItems = [];
        if (!empty($residentCreditItems)) {
            /** @var ResidentCreditItem $residentCreditItem */
            foreach ($residentCreditItems as $residentCreditItem) {
                $start = $residentCreditItem->getStart();
                $end = $residentCreditItem->getEnd();

                if ($start <= $createdAt && ($end === null || $end >= $createdAt)) {
                    $filteredCreditItems[] = $residentCreditItem;
                }
            }
        }

        return $filteredCreditItems;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("resident_discount_items")
     * @Serializer\Groups({
     *     "api_admin_resident_ledger_get"
     * })
     * @return mixed
     */
    public function getDiscountItems()
    {
        $createdAt = $this->getCreatedAt();
        $residentDiscountItems = $this->resident->getResidentDiscountItems();

        $filteredDiscountItems = [];
        if (!empty($residentDiscountItems)) {
            /** @var ResidentDiscountItem $residentDiscountItem */
            foreach ($residentDiscountItems as $residentDiscountItem) {
                $start = $residentDiscountItem->getStart();
                $end = $residentDiscountItem->getEnd();

                if ($start <= $createdAt && ($end === null || $end >= $createdAt)) {
                    $filteredDiscountItems[] = $residentDiscountItem;
                }
            }
        }

        return $filteredDiscountItems;
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
     * @return array
     */
    public function getAwayDays(): array
    {
        return $this->awayDays;
    }

    /**
     * @param array $awayDays
     */
    public function setAwayDays(array $awayDays): void
    {
        $this->awayDays = $awayDays;
    }

    /**
     * @return mixed
     */
    public function getResidentPrivatePayPaymentReceivedItems()
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
     * @param ResidentPrivatePayPaymentReceivedItem $residentPrivatePayPaymentReceivedItem
     */
    public function addResidentPrivatePayPaymentReceivedItem($residentPrivatePayPaymentReceivedItem): void
    {
        $residentPrivatePayPaymentReceivedItem->setLedger($this);
        $this->residentPrivatePayPaymentReceivedItems->add($residentPrivatePayPaymentReceivedItem);
    }

    /**
     * @param ResidentPrivatePayPaymentReceivedItem $residentPrivatePayPaymentReceivedItem
     */
    public function removeResidentPrivatePayPaymentReceivedItem($residentPrivatePayPaymentReceivedItem): void
    {
        $this->residentPrivatePayPaymentReceivedItems->removeElement($residentPrivatePayPaymentReceivedItem);
    }

    /**
     * @return mixed
     */
    public function getResidentNotPrivatePayPaymentReceivedItems()
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

    /**
     * @param ResidentNotPrivatePayPaymentReceivedItem $residentNotPrivatePayPaymentReceivedItem
     */
    public function addResidentNotPrivatePayPaymentReceivedItem($residentNotPrivatePayPaymentReceivedItem): void
    {
        $residentNotPrivatePayPaymentReceivedItem->setLedger($this);
        $this->residentNotPrivatePayPaymentReceivedItems->add($residentNotPrivatePayPaymentReceivedItem);
    }

    /**
     * @param ResidentNotPrivatePayPaymentReceivedItem $residentNotPrivatePayPaymentReceivedItem
     */
    public function removeResidentNotPrivatePayPaymentReceivedItem($residentNotPrivatePayPaymentReceivedItem): void
    {
        $this->residentNotPrivatePayPaymentReceivedItems->removeElement($residentNotPrivatePayPaymentReceivedItem);
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
