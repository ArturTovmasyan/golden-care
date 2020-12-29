<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use App\Model\SourcePeriod;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class PaymentSource
 *
 * @ORM\Entity(repositoryClass="App\Repository\PaymentSourceRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_payment_source_add",
 *          "api_admin_payment_source_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_payment_source")
 * @Grid(
 *     api_admin_payment_source_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ps.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "ps.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "private_pay",
 *              "type"       = "boolean",
 *              "field"      = "ps.privatePay"
 *          },
 *          {
 *              "id"         = "period",
 *              "type"       = "enum",
 *              "field"      = "ps.period",
 *              "values"     = "\App\Model\SourcePeriod::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "amount",
 *              "type"       = "number",
 *              "field"      = "ps.amount"
 *          },
 *          {
 *              "id"         = "care_level_adjustment",
 *              "type"       = "boolean",
 *              "field"      = "ps.careLevelAdjustment"
 *          },
 *          {
 *              "id"         = "only_for_occupied_days",
 *              "type"       = "boolean",
 *              "field"      = "ps.onlyForOccupiedDays"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class PaymentSource
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get",
     *     "api_admin_payment_source_base_rate_list",
     *     "api_admin_payment_source_base_rate_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_payment_source_add",
     *     "api_admin_payment_source_edit"
     * })
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_payment_source_add",
     *          "api_admin_payment_source_edit"
     * })
     * @ORM\Column(name="title", type="string", length=50)
     * @Groups({
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get",
     *     "api_admin_payment_source_base_rate_list",
     *     "api_admin_payment_source_base_rate_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $title;


    /**
     * @var bool
     * @ORM\Column(name="private_pay", type="boolean")
     * @Groups({
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $privatePay;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_payment_source_add",
     *     "api_admin_payment_source_edit"
     * })
     * @Assert\Choice(
     *     callback={"App\Model\SourcePeriod","getTypeValues"},
     *     groups={
     *         "api_admin_payment_source_add",
     *         "api_admin_payment_source_edit"
     * })
     * @ORM\Column(name="rent_period", type="integer", length=1)
     * @Groups({
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $period = SourcePeriod::MONTHLY;

    /**
     * @var float
     * @ORM\Column(name="amount", type="float", length=10, nullable=true)
     * @Assert\NotBlank(groups={
     *     "api_admin_payment_source_amount_add",
     *     "api_admin_payment_source_amount_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^0$)|(^[1-9][0-9]*$)|(^[0-9]+(\.[0-9]{1,2})$)/",
     *      message="The value entered is not a valid type. Examples of valid entries: '2000, 0.55, 100.34'.",
     *      groups={
     *          "api_admin_payment_source_amount_add",
     *          "api_admin_payment_source_amount_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_payment_source_amount_add",
     *          "api_admin_payment_source_amount_edit"
     * })
     * @Groups({
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $amount;

    /**
     * @var bool
     * @ORM\Column(name="care_level_adjustment", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $careLevelAdjustment;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentSourceBaseRate", mappedBy="paymentSource", cascade={"persist"})
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $baseRates;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_payment_source_add",
     *     "api_admin_payment_source_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="paymentSources")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $space;

    /**
     * @var bool
     * @ORM\Column(name="resident_name", type="boolean")
     * @Groups({
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $residentName;

    /**
     * @var bool
     * @ORM\Column(name="date_of_birth", type="boolean")
     * @Groups({
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $dateOfBirth;

    /**
     * @var string
     * @Assert\Length(
     *      max = 32,
     *      maxMessage = "Field Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_payment_source_add",
     *          "api_admin_payment_source_edit"
     * })
     * @ORM\Column(name="field_name", type="string", length=32, nullable=true)
     * @Groups({
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $fieldName;

    /**
     * @var bool
     * @ORM\Column(name="only_for_occupied_days", type="boolean")
     * @Groups({
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $onlyForOccupiedDays;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Lead", mappedBy="paymentType", cascade={"persist"})
     */
    private $leads;

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("levels")
     * @Serializer\Groups({
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     * @return Collection|PaymentSourceBaseRate[]|null
     */
    public function getRates()
    {
        $now = new \DateTime('now');

        $criteria = Criteria::create()
            ->where(Criteria::expr()->lt('date', $now))
            ->orderBy(array('date' => Criteria::DESC))
            ->setMaxResults(1)
        ;

        /** @var PaymentSourceBaseRate[] $data */
        $data = $this->baseRates->matching($criteria);

        if(\count($data) > 0) {
            $data = $data[0]->getLevels();
        }

        return $data;
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
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = preg_replace('/\s\s+/', ' ', $title);
    }

    /**
     * @return bool
     */
    public function isPrivatePay(): bool
    {
        return $this->privatePay;
    }

    /**
     * @param bool $privatePay
     */
    public function setPrivatePay(bool $privatePay): void
    {
        $this->privatePay = $privatePay;
    }

    /**
     * @return int|null
     */
    public function getPeriod(): ?int
    {
        return $this->period;
    }

    /**
     * @param int|null $period
     */
    public function setPeriod(?int $period): void
    {
        $this->period = $period;
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
     * @return bool
     */
    public function isCareLevelAdjustment(): bool
    {
        return $this->careLevelAdjustment;
    }

    /**
     * @param bool $careLevelAdjustment
     */
    public function setCareLevelAdjustment(bool $careLevelAdjustment): void
    {
        $this->careLevelAdjustment = $careLevelAdjustment;
    }

    /**
     * @return mixed
     */
    public function getBaseRates()
    {
        return $this->baseRates;
    }

    /**
     * @param mixed $baseRates
     */
    public function setBaseRates($baseRates): void
    {
        $this->baseRates = $baseRates;
    }

    /**
     * @return Space|null
     */
    public function getSpace(): ?Space
    {
        return $this->space;
    }

    /**
     * @param Space|null $space
     */
    public function setSpace(?Space $space): void
    {
        $this->space = $space;
    }

    /**
     * @return bool
     */
    public function isResidentName(): bool
    {
        return $this->residentName;
    }

    /**
     * @param bool $residentName
     */
    public function setResidentName(bool $residentName): void
    {
        $this->residentName = $residentName;
    }

    /**
     * @return bool
     */
    public function isDateOfBirth(): bool
    {
        return $this->dateOfBirth;
    }

    /**
     * @param bool $dateOfBirth
     */
    public function setDateOfBirth(bool $dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    /**
     * @return string|null
     */
    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }

    /**
     * @param string|null $fieldName
     */
    public function setFieldName(?string $fieldName): void
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @return bool
     */
    public function isOnlyForOccupiedDays(): bool
    {
        return $this->onlyForOccupiedDays;
    }

    /**
     * @param bool $onlyForOccupiedDays
     */
    public function setOnlyForOccupiedDays(bool $onlyForOccupiedDays): void
    {
        $this->onlyForOccupiedDays = $onlyForOccupiedDays;
    }

    /**
     * @return ArrayCollection
     */
    public function getLeads(): ArrayCollection
    {
        return $this->leads;
    }

    /**
     * @param ArrayCollection $leads
     */
    public function setLeads(ArrayCollection $leads): void
    {
        $this->leads = $leads;
    }
}
