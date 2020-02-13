<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use App\Annotation\Grid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentSourceBaseRateRepository")
 * @ORM\Table(name="tbl_payment_source_base_rate")
 * @Grid(
 *     api_admin_payment_source_base_rate_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "sbr.id"
 *          },
 *          {
 *              "id"         = "payment_source",
 *              "type"       = "string",
 *              "field"      = "ps.title"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "sbr.date",
 *          },
 *          {
 *              "id"         = "base_rates",
 *              "sortable"   = false,
 *              "type"       = "json_sorted",
 *              "field"      = "base_rates"
 *          }
 *     }
 * )
 */
class PaymentSourceBaseRate
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_payment_source_base_rate_list",
     *     "api_admin_payment_source_base_rate_get"
     * })
     */
    private $id;

    /**
     * @var PaymentSource
     * @ORM\ManyToOne(targetEntity="App\Entity\PaymentSource", inversedBy="baseRates", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_payment_source", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Payment Source",
     *      groups={
     *          "api_admin_payment_source_base_rate_edit",
     *          "api_admin_payment_source_base_rate_add"
     *      }
     * )
     * @Groups({
     *     "api_admin_payment_source_base_rate_list",
     *     "api_admin_payment_source_base_rate_get"
     * })
     */
    private $paymentSource;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_payment_source_base_rate_add",
     *     "api_admin_payment_source_base_rate_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_payment_source_base_rate_add",
     *     "api_admin_payment_source_base_rate_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_admin_payment_source_base_rate_get",
     *     "api_admin_payment_source_base_rate_list"
     * })
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentSourceBaseRateCareLevel", mappedBy="baseRate", cascade={"persist"})
     * @Serializer\SerializedName("base_rates")
     * @Groups({
     *     "api_admin_payment_source_base_rate_get",
     *     "api_admin_payment_source_base_rate_list",
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $levels;

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
     * @return PaymentSource|null
     */
    public function getPaymentSource(): ?PaymentSource
    {
        return $this->paymentSource;
    }

    /**
     * @param PaymentSource|null $paymentSource
     */
    public function setPaymentSource(?PaymentSource $paymentSource): void
    {
        $this->paymentSource = $paymentSource;
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
     * @return mixed
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * @param mixed $levels
     */
    public function setLevels($levels): void
    {
        $this->levels = $levels;
    }
}
