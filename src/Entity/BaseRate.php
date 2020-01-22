<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BaseRateRepository")
 * @ORM\Table(name="tbl_facility_room_type_care_level")
 */
class BaseRate
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_facility_room_type_list",
     *     "api_admin_facility_room_type_get"
     * })
     */
    private $id;

    /**
     * @var FacilityRoomType
     * @ORM\ManyToOne(targetEntity="App\Entity\FacilityRoomType", inversedBy="baseRates", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_facility_room_type", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Room Type",
     *      groups={
     *          "api_admin_base_rate_edit",
     *          "api_admin_base_rate_add"
     *      }
     * )
     */
    private $roomType;

    /**
     * @var CareLevel
     * @ORM\ManyToOne(targetEntity="App\Entity\CareLevel", inversedBy="baseRates", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_care_level", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Care Level",
     *      groups={
     *          "api_admin_base_rate_edit",
     *          "api_admin_base_rate_add"
     *      }
     * )
     * @Groups({
     *      "api_admin_facility_room_type_list",
     *      "api_admin_facility_room_type_get"
     * })
     */
    private $careLevel;

    /**
     * @var float
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\NotBlank(groups={
     *     "api_admin_base_rate_add",
     *     "api_admin_base_rate_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^0$)|(^[1-9][0-9]*$)|(^[0-9]+(\.[0-9]{1,2})$)/",
     *      message="The value entered is not a valid type. Examples of valid entries: '2000, 0.55, 100.34'.",
     *      groups={
     *          "api_admin_base_rate_add",
     *          "api_admin_base_rate_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_base_rate_add",
     *          "api_admin_base_rate_edit"
     * })
     * @Groups({
     *     "api_admin_facility_room_type_list",
     *     "api_admin_facility_room_type_get"
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
     * @return FacilityRoomType|null
     */
    public function getRoomType(): ?FacilityRoomType
    {
        return $this->roomType;
    }

    /**
     * @param FacilityRoomType|null $roomType
     */
    public function setRoomType(?FacilityRoomType $roomType): void
    {
        $this->roomType = $roomType;
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
