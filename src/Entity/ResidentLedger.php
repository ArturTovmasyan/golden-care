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
     *     "api_admin_resident_credit_discount_item_list",
     *     "api_admin_resident_credit_discount_item_get",
     *     "api_admin_resident_payment_received_item_list",
     *     "api_admin_resident_payment_received_item_get"
     *
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
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentExpenseItem", mappedBy="ledger", cascade={"remove", "persist"})
     */
    private $residentExpenseItems;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentCreditDiscountItem", mappedBy="ledger", cascade={"remove", "persist"})
     */
    private $residentCreditDiscountItems;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentPaymentReceivedItem", mappedBy="ledger", cascade={"remove", "persist"})
     */
    private $residentPaymentReceivedItems;

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
     * @return ArrayCollection
     */
    public function getResidentCreditDiscountItems(): ArrayCollection
    {
        return $this->residentCreditDiscountItems;
    }

    /**
     * @param ArrayCollection $residentCreditDiscountItems
     */
    public function setResidentCreditDiscountItems(ArrayCollection $residentCreditDiscountItems): void
    {
        $this->residentCreditDiscountItems = $residentCreditDiscountItems;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentPaymentReceivedItems(): ArrayCollection
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
