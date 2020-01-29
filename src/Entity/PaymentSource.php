<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use App\Model\SourcePeriod;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
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
 *              "id"         = "away_reduction",
 *              "type"       = "boolean",
 *              "field"      = "ps.awayReduction"
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
 *              "id"         = "base_rates",
 *              "sortable"   = false,
 *              "type"       = "json",
 *              "field"      = "base_rates"
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
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $title;

    /**
     * @var bool
     * @ORM\Column(name="away_reduction", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $awayReduction;

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
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_payment_source_add",
     *     "api_admin_payment_source_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^0$)|(^[1-9][0-9]*$)|(^[0-9]+(\.[0-9]{1,2})$)/",
     *      message="The value entered is not a valid type. Examples of valid entries: '2000, 0.55, 100.34'.",
     *      groups={
     *          "api_admin_payment_source_add",
     *          "api_admin_payment_source_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_payment_source_add",
     *          "api_admin_payment_source_edit"
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
     * @ORM\OneToMany(targetEntity="App\Entity\SourceBaseRate", mappedBy="paymentSource", cascade={"persist"})
     * @Groups({
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
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
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Lead", mappedBy="paymentType", cascade={"remove", "persist"})
     */
    private $leads;

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
    public function isAwayReduction(): bool
    {
        return $this->awayReduction;
    }

    /**
     * @param bool $awayReduction
     */
    public function setAwayReduction(bool $awayReduction): void
    {
        $this->awayReduction = $awayReduction;
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
