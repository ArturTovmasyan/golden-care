<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentSourceBaseRateCareLevelRepository")
 * @ORM\Table(name="tbl_payment_source_base_rate_care_level")
 */
class PaymentSourceBaseRateCareLevel
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_payment_source_base_rate_get",
     *     "api_admin_payment_source_base_rate_list",
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $id;

    /**
     * @var PaymentSourceBaseRate
     * @ORM\ManyToOne(targetEntity="App\Entity\PaymentSourceBaseRate", inversedBy="levels", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_payment_source_base_rate", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Base Rate",
     *      groups={
     *          "api_admin_payment_source_base_rate_edit",
     *          "api_admin_payment_source_base_rate_add"
     *      }
     * )
     */
    private $baseRate;

    /**
     * @var CareLevel
     * @ORM\ManyToOne(targetEntity="App\Entity\CareLevel", inversedBy="sourceBaseRates", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_care_level", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Care Level",
     *      groups={
     *          "api_admin_payment_source_base_rate_edit",
     *          "api_admin_payment_source_base_rate_add"
     *      }
     * )
     * @Groups({
     *      "api_admin_payment_source_base_rate_get",
     *      "api_admin_payment_source_base_rate_list",
     *      "api_admin_payment_source_list",
     *      "api_admin_payment_source_get"
     * })
     */
    private $careLevel;

    /**
     * @var float
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_payment_source_base_rate_add",
     *     "api_admin_payment_source_base_rate_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^0$)|(^[1-9][0-9]*$)|(^[0-9]+(\.[0-9]{1,2})$)/",
     *      message="The value entered is not a valid type. Examples of valid entries: '2000, 0.55, 100.34'.",
     *      groups={
     *          "api_admin_payment_source_base_rate_add",
     *          "api_admin_payment_source_base_rate_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_payment_source_base_rate_add",
     *          "api_admin_payment_source_base_rate_edit"
     * })
     * @Groups({
     *     "api_admin_payment_source_base_rate_get",
     *     "api_admin_payment_source_base_rate_list",
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $amount;

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
     * @return PaymentSourceBaseRate|null
     */
    public function getBaseRate(): ?PaymentSourceBaseRate
    {
        return $this->baseRate;
    }

    /***
     * @param PaymentSourceBaseRate|null $baseRate
     */
    public function setBaseRate(?PaymentSourceBaseRate $baseRate): void
    {
        $this->baseRate = $baseRate;
    }

    /**
     * @return CareLevel|null
     */
    public function getCareLevel(): ?CareLevel
    {
        return $this->careLevel;
    }

    /**
     * @param CareLevel|null $careLevel
     */
    public function setCareLevel(?CareLevel $careLevel): void
    {
        $this->careLevel = $careLevel;
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
}
