<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FacilityRoomBaseRateCareLevelRepository")
 * @ORM\Table(name="tbl_facility_room_type_base_rate_care_level")
 */
class FacilityRoomBaseRateCareLevel
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_facility_room_base_rate_get",
     *     "api_admin_facility_room_base_rate_list",
     *     "api_admin_facility_room_type_list",
     *     "api_admin_facility_room_type_get",
     *     "api_admin_resident_admission_get_active"
     * })
     */
    private $id;

    /**
     * @var FacilityRoomBaseRate
     * @ORM\ManyToOne(targetEntity="App\Entity\FacilityRoomBaseRate", inversedBy="levels", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_facility_room_base_rate", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Base Rate",
     *      groups={
     *          "api_admin_facility_room_base_rate_edit",
     *          "api_admin_facility_room_base_rate_add"
     *      }
     * )
     */
    private $baseRate;

    /**
     * @var CareLevel
     * @ORM\ManyToOne(targetEntity="App\Entity\CareLevel", inversedBy="baseRates", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_care_level", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Care Level",
     *      groups={
     *          "api_admin_facility_room_base_rate_edit",
     *          "api_admin_facility_room_base_rate_add"
     *      }
     * )
     * @Groups({
     *      "api_admin_facility_room_base_rate_get",
     *      "api_admin_facility_room_base_rate_list",
     *      "api_admin_resident_admission_get_active"
     * })
     */
    private $careLevel;

    /**
     * @var float
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\GreaterThan(
     *      value = 0,
     *      groups={
     *          "api_admin_facility_room_base_rate_edit",
     *          "api_admin_facility_room_base_rate_add"
     *      }
     * )
     * @Assert\LessThan(
     *      value = 1000000,
     *      groups={
     *          "api_admin_facility_room_base_rate_edit",
     *          "api_admin_facility_room_base_rate_add"
     *      }
     * )
     * @Groups({
     *     "api_admin_facility_room_base_rate_get",
     *     "api_admin_facility_room_base_rate_list",
     *     "api_admin_resident_admission_get_active"
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
     * @return FacilityRoomBaseRate|null
     */
    public function getBaseRate(): ?FacilityRoomBaseRate
    {
        return $this->baseRate;
    }

    /**
     * @param FacilityRoomBaseRate|null $baseRate
     */
    public function setBaseRate(?FacilityRoomBaseRate $baseRate): void
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
