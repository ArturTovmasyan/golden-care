<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentKeyFinanceDate
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentKeyFinanceDateRepository")
 * @ORM\Table(name="tbl_resident_key_finance_date")
 * @Grid(
 *     api_admin_resident_key_finance_date_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rkfd.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "kft.title"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "rkfd.date"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(kft.description, 1, 100)), CASE WHEN LENGTH(kft.description) > 100 THEN '…' ELSE '' END)",
 *              "sortable"   = false,
 *              "width"      = "10rem"
 *          }
 *     }
 * )
 */
class ResidentKeyFinanceDate
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_key_finance_date_list",
     *     "api_admin_resident_key_finance_date_get"
     * })
     */
    private $id;

    /**
     * @var ResidentLedger
     * @Assert\NotNull(message = "Please select a Ledger", groups={
     *     "api_admin_resident_key_finance_date_add",
     *     "api_admin_resident_key_finance_date_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\ResidentLedger", inversedBy="residentKeyFinanceDates")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ledger", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_key_finance_date_list",
     *     "api_admin_resident_key_finance_date_get"
     * })
     */
    private $ledger;

    /**
     * @var KeyFinanceType
     * @Assert\NotNull(message = "Please select a Payment Type", groups={
     *     "api_admin_resident_key_finance_date_add",
     *     "api_admin_resident_key_finance_date_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\KeyFinanceType", inversedBy="residentKeyFinanceDates")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_key_finance_type", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_key_finance_date_list",
     *     "api_admin_resident_key_finance_date_get"
     * })
     */
    private $keyFinanceType;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_key_finance_date_add",
     *     "api_admin_resident_key_finance_date_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_resident_key_finance_date_add",
     *     "api_admin_resident_key_finance_date_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_admin_resident_key_finance_date_list",
     *     "api_admin_resident_key_finance_date_get"
     * })
     */
    private $date;

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
     * @return KeyFinanceType|null
     */
    public function getKeyFinanceType(): ?KeyFinanceType
    {
        return $this->keyFinanceType;
    }

    /**
     * @param KeyFinanceType|null $keyFinanceType
     */
    public function setKeyFinanceType(?KeyFinanceType $keyFinanceType): void
    {
        $this->keyFinanceType = $keyFinanceType;
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
}
