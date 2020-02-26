<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FacilityRoomBaseRateRepository")
 * @ORM\Table(name="tbl_facility_room_type_base_rate")
 * @Grid(
 *     api_admin_facility_room_base_rate_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "br.id"
 *          },
 *          {
 *              "id"         = "room_type",
 *              "type"       = "string",
 *              "field"      = "frt.title"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "br.date",
 *          },
 *          {
 *              "id"         = "base_rates",
 *              "sortable"   = false,
 *              "type"       = "json_sorted_horizontal",
 *              "field"      = "base_rates"
 *          }
 *     }
 * )
 */
class FacilityRoomBaseRate
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
     *     "api_admin_resident_admission_get_active"
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
     *          "api_admin_facility_room_base_rate_edit",
     *          "api_admin_facility_room_base_rate_add"
     *      }
     * )
     * @Groups({
     *     "api_admin_facility_room_base_rate_get",
     *     "api_admin_facility_room_base_rate_list"
     * })
     */
    private $roomType;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_room_base_rate_add",
     *     "api_admin_facility_room_base_rate_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_facility_room_base_rate_add",
     *     "api_admin_facility_room_base_rate_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_admin_facility_room_base_rate_get",
     *     "api_admin_facility_room_base_rate_list",
     *     "api_admin_resident_admission_get_active"
     * })
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FacilityRoomBaseRateCareLevel", mappedBy="baseRate", cascade={"persist"})
     * @Assert\Valid(groups={
     *      "api_admin_facility_room_base_rate_add",
     *      "api_admin_facility_room_base_rate_edit"
     * })
     * @Groups({
     *     "api_admin_facility_room_base_rate_get",
     *     "api_admin_facility_room_base_rate_list",
     *     "api_admin_resident_admission_get_active"
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
