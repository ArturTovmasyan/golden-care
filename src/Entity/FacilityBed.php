<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class FacilityBed
 *
 * @ORM\Entity(repositoryClass="App\Repository\FacilityRoomRepository")
 * @UniqueEntity(
 *     fields={"room", "number"},
 *     errorPath="number",
 *     message="This number is already in use on that room",
 *     groups={
 *          "api_admin_bed_add",
 *          "api_admin_bed_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_facility_bed")
 * @Grid(
 *     api_admin_bed_grid={
 *          {"id", "number", true, true, "fb.id"},
 *          {"number", "string", true, true, "fb.number"},
 *          {"room", "string", true, true, "fr.name"},
 *     }
 * )
 */
class FacilityBed
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_bed_grid",
     *     "api_admin_bed_list",
     *     "api_admin_bed_get",
     *     "api_admin_facility_room_grid",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_admin_bed_add",
     *          "api_admin_bed_edit",
     *          "api_admin_facility_room_add",
     *          "api_admin_facility_room_edit"
     *     }
     * )
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Number cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_bed_add",
     *          "api_admin_bed_edit",
     *          "api_admin_facility_room_add",
     *          "api_admin_facility_room_edit"
     *      }
     * )
     * @ORM\Column(name="number", type="string", length=10)
     * @Groups({
     *     "api_admin_bed_grid",
     *     "api_admin_bed_list",
     *     "api_admin_bed_get",
     *     "api_admin_facility_room_grid",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get"
     * })
     */
    private $number;

    /**
     * @var FacilityRoom
     * @Assert\NotNull(
     *     message = "Please select a FacilityRoom",
     *     groups={
     *          "api_admin_bed_add",
     *          "api_admin_bed_edit",
     *          "api_admin_facility_room_add",
     *          "api_admin_facility_room_edit"
     *     }
     * )
     * @ORM\ManyToOne(targetEntity="App\Entity\FacilityRoom", inversedBy="beds", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_facility_room", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_bed_grid", "api_admin_bed_list", "api_admin_bed_get"})
     */
    private $room;

    /**
     * @return int
     */
    public function getId(): int
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
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @param null|string $number
     */
    public function setNumber(?string $number): void
    {
        $this->number = preg_replace('/\s\s+/', ' ', $number);
    }

    /**
     * @return FacilityRoom|null
     */
    public function getRoom(): ?FacilityRoom
    {
        return $this->room;
    }

    /**
     * @param FacilityRoom|null $room
     * @return FacilityBed
     */
    public function setRoom(?FacilityRoom $room): self
    {
        $this->room = $room;

        return $this;
    }
}
