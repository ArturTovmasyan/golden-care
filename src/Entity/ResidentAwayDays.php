<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentAwayDays
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentAwayDaysRepository")
 * @ORM\Table(name="tbl_resident_away_days")
 * @Grid(
 *     api_admin_resident_away_days_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rad.id"
 *          },
 *          {
 *              "id"         = "start",
 *              "type"       = "date",
 *              "field"      = "rad.start"
 *          },
 *          {
 *              "id"         = "end",
 *              "type"       = "date",
 *              "field"      = "rad.end"
 *          },
 *          {
 *              "id"         = "reason",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(rad.reason, 1, 100)), CASE WHEN LENGTH(rad.reason) > 100 THEN '…' ELSE '' END)",
 *              "sortable"   = false,
 *              "width"      = "10rem"
 *          }
 *     }
 * )
 */
class ResidentAwayDays
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_away_days_list",
     *     "api_admin_resident_away_days_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $id;

    /**
     * @var ResidentLedger
     * @Assert\NotNull(message = "Please select a Ledger", groups={
     *     "api_admin_resident_away_days_add",
     *     "api_admin_resident_away_days_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\ResidentLedger", inversedBy="residentAwayDays")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ledger", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_away_days_list",
     *     "api_admin_resident_away_days_get"
     * })
     */
    private $ledger;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_away_days_add",
     *     "api_admin_resident_away_days_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_resident_away_days_add",
     *     "api_admin_resident_away_days_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @ORM\Column(name="start", type="datetime")
     * @Groups({
     *     "api_admin_resident_away_days_list",
     *     "api_admin_resident_away_days_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $start;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_away_days_add",
     *     "api_admin_resident_away_days_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_resident_away_days_add",
     *     "api_admin_resident_away_days_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @ORM\Column(name="end", type="datetime")
     * @Groups({
     *     "api_admin_resident_away_days_list",
     *     "api_admin_resident_away_days_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $end;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_away_days_add",
     *     "api_admin_resident_away_days_edit",
     *     "api_admin_resident_ledger_edit"
     * })
     * @Assert\Length(
     *      max = 128,
     *      maxMessage = "Reason cannot be longer than {{ limit }} characters",
     *      groups={
     *           "api_admin_resident_away_days_add",
     *           "api_admin_resident_away_days_edit",
     *           "api_admin_resident_ledger_edit"
     * })
     * @ORM\Column(name="reason", type="string", length=128)
     * @Groups({
     *     "api_admin_resident_away_days_list",
     *     "api_admin_resident_away_days_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $reason;

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
     * @return \DateTime|null
     */
    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    /**
     * @param \DateTime|null $start
     */
    public function setStart(?\DateTime $start): void
    {
        $this->start = $start;
    }

    /**
     * @return \DateTime|null
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime|null $end
     */
    public function setEnd(?\DateTime $end): void
    {
        $this->end = $end;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @param string|null $reason
     */
    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }
}
