<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentRentIncrease
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentRentIncreaseRepository")
 * @ORM\Table(name="tbl_resident_rent_increase")
 * @Grid(
 *     api_admin_resident_rent_increase_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rri.id"
 *          },
 *          {
 *              "id"         = "reason",
 *              "type"       = "string",
 *              "field"      = "rrn.title"
 *          },
 *          {
 *              "id"         = "amount",
 *              "type"       = "number",
 *              "field"      = "rri.amount"
 *          },
 *          {
 *              "id"         = "effective_date",
 *              "type"       = "date",
 *              "field"      = "rri.effectiveDate"
 *          },
 *          {
 *              "id"         = "notification_date",
 *              "type"       = "date",
 *              "field"      = "rri.notificationDate"
 *          }
 *     }
 * )
 */
class ResidentRentIncrease
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_rent_increase_list",
     *     "api_admin_resident_rent_increase_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_resident_rent_increase_add",
     *     "api_admin_resident_rent_increase_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentRentIncreases")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_rent_increase_list",
     *     "api_admin_resident_rent_increase_get"
     * })
     */
    private $resident;

    /**
     * @var RentReason
     * @Assert\NotNull(message = "Please select a Rent Reason", groups={
     *     "api_admin_resident_rent_increase_add",
     *     "api_admin_resident_rent_increase_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\RentReason", inversedBy="residentRentIncreases")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_rent_reason", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_rent_increase_list",
     *     "api_admin_resident_rent_increase_get"
     * })
     */
    private $reason;

    /**
     * @var float
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_rent_increase_add",
     *     "api_admin_resident_rent_increase_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^0$)|(^[1-9][0-9]*$)|(^[0-9]+(\.[0-9]{1,2})$)/",
     *      message="The value entered is not a valid type. Examples of valid entries: '2000, 0.55, 100.34'.",
     *      groups={
     *          "api_admin_resident_rent_increase_add",
     *          "api_admin_resident_rent_increase_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_rent_increase_add",
     *          "api_admin_resident_rent_increase_edit"
     * })
     * @Groups({
     *     "api_admin_resident_rent_increase_list",
     *     "api_admin_resident_rent_increase_get"
     * })
     */
    private $amount;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_rent_increase_add",
     *     "api_admin_resident_rent_increase_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_resident_rent_increase_add",
     *     "api_admin_resident_rent_increase_edit"
     * })
     * @ORM\Column(name="effective_date", type="datetime")
     * @Groups({
     *     "api_admin_resident_rent_increase_list",
     *     "api_admin_resident_rent_increase_get"
     * })
     */
    private $effectiveDate;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_rent_increase_add",
     *     "api_admin_resident_rent_increase_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_resident_rent_increase_add",
     *     "api_admin_resident_rent_increase_edit"
     * })
     * @ORM\Column(name="notification_date", type="datetime")
     * @Groups({
     *     "api_admin_resident_rent_increase_list",
     *     "api_admin_resident_rent_increase_get"
     * })
     */
    private $notificationDate;

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
     * @return \DateTime|null
     */
    public function getEffectiveDate(): ?\DateTime
    {
        return $this->effectiveDate;
    }

    /**
     * @param \DateTime|null $effectiveDate
     */
    public function setEffectiveDate(?\DateTime $effectiveDate): void
    {
        $this->effectiveDate = $effectiveDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getNotificationDate(): ?\DateTime
    {
        return $this->notificationDate;
    }

    /**
     * @param \DateTime|null $notificationDate
     */
    public function setNotificationDate(?\DateTime $notificationDate): void
    {
        $this->notificationDate = $notificationDate;
    }
}
