<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class FacilityBed
 *
 * @ORM\Entity(repositoryClass="App\Repository\FacilityBedRepository")
 * @UniqueEntity(
 *     fields={"room", "number"},
 *     errorPath="number",
 *     message="This number is already in use on that room",
 *     groups={
 *          "api_admin_facility_bed_add",
 *          "api_admin_facility_bed_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_facility_bed")
 * @Grid(
 *     api_admin_facility_bed_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "fb.id"
 *          },
 *          {
 *              "id"         = "number",
 *              "type"       = "string",
 *              "field"      = "fb.number"
 *          },
 *          {
 *              "id"         = "room",
 *              "type"       = "string",
 *              "field"      = "fr.name"
 *          }
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
     *     "api_admin_facility_bed_grid",
     *     "api_admin_facility_bed_list",
     *     "api_admin_facility_bed_get",
     *     "api_admin_facility_room_grid",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_admin_facility_bed_add",
     *          "api_admin_facility_bed_edit",
     *          "api_admin_facility_room_add",
     *          "api_admin_facility_room_edit"
     *     }
     * )
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Number cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_facility_bed_add",
     *          "api_admin_facility_bed_edit",
     *          "api_admin_facility_room_add",
     *          "api_admin_facility_room_edit"
     *      }
     * )
     * @ORM\Column(name="number", type="string", length=10)
     * @Groups({
     *     "api_admin_facility_bed_grid",
     *     "api_admin_facility_bed_list",
     *     "api_admin_facility_bed_get",
     *     "api_admin_facility_room_grid",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active"
     * })
     */
    private $number;

    /**
     * @var FacilityRoom
     * @Assert\NotNull(
     *     message = "Please select a FacilityRoom",
     *     groups={
     *          "api_admin_facility_bed_add",
     *          "api_admin_facility_bed_edit",
     *          "api_admin_facility_room_add",
     *          "api_admin_facility_room_edit"
     *     }
     * )
     * @ORM\ManyToOne(targetEntity="App\Entity\FacilityRoom", inversedBy="beds", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_facility_room", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_facility_bed_grid",
     *     "api_admin_facility_bed_list",
     *     "api_admin_facility_bed_get",
     *     "api_admin_contract_get_active",
     *     "api_admin_contract_get"
     * })
     */
    private $room;

    /**
     * @var Resident
     * @Groups({
     *     "api_admin_facility_bed_list",
     *     "api_admin_facility_bed_get",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get"
     * })
     */
    private $resident;

    /**
     * @var bool
     * @ORM\Column(name="enabled", type="boolean", options={"default" = 1})
     * @Assert\NotNull(groups={
     *          "api_admin_facility_bed_add",
     *          "api_admin_facility_bed_edit",
     *          "api_admin_facility_room_add",
     *          "api_admin_facility_room_edit"
     * })
     * @Groups({
     *     "api_admin_facility_bed_list",
     *     "api_admin_facility_bed_get",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get"
     * })
     */
    private $enabled;

    /**
     * @return int
     */
    public function getId()
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
     */
    public function setRoom(?FacilityRoom $room): void
    {
        $this->room = $room;
    }

    /**
     * @return Resident|null
     */
    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    /**
     * @param Resident|null $resident
     */
    public function setResident(?Resident $resident): void
    {
        $this->resident = $resident;
    }

    /**
     * @return bool
     */
    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(?bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
