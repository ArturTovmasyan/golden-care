<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ApartmentBed
 *
 * @ORM\Entity(repositoryClass="App\Repository\ApartmentBedRepository")
 * @UniqueEntity(
 *     fields={"room", "number"},
 *     errorPath="number",
 *     message="The number is already in use for this room.",
 *     groups={
 *          "api_admin_apartment_bed_add",
 *          "api_admin_apartment_bed_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_apartment_bed")
 * @Grid(
 *     api_admin_apartment_bed_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ab.id"
 *          },
 *          {
 *              "id"         = "apartment_name",
 *              "type"       = "string",
 *              "field"      = "a.name"
 *          },
 *          {
 *              "id"         = "apartment_shorthand",
 *              "type"       = "string",
 *              "field"      = "a.shorthand"
 *          },
 *          {
 *              "id"         = "floor",
 *              "type"       = "string",
 *              "field"      = "ar.floor"
 *          },
 *          {
 *              "id"         = "room",
 *              "type"       = "string",
 *              "field"      = "ar.number"
 *          },
 *          {
 *              "id"         = "number",
 *              "type"       = "string",
 *              "field"      = "ab.number"
 *          },
 *          {
 *              "id"         = "enabled",
 *              "type"       = "boolean",
 *              "field"      = "ab.enabled"
 *          },
 *          {
 *              "id"         = "resident",
 *              "type"       = "string",
 *              "field"      = ""
 *          }
 *     }
 * )
 */
class ApartmentBed
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_apartment_bed_grid",
     *     "api_admin_apartment_bed_list",
     *     "api_admin_apartment_bed_get",
     *     "api_admin_apartment_room_grid",
     *     "api_admin_apartment_room_list",
     *     "api_admin_apartment_room_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_admin_apartment_bed_add",
     *          "api_admin_apartment_bed_edit",
     *          "api_admin_apartment_room_add",
     *          "api_admin_apartment_room_edit"
     *     }
     * )
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Number cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_apartment_bed_add",
     *          "api_admin_apartment_bed_edit",
     *          "api_admin_apartment_room_add",
     *          "api_admin_apartment_room_edit"
     *      }
     * )
     * @ORM\Column(name="number", type="string", length=10)
     * @Groups({
     *     "api_admin_apartment_bed_grid",
     *     "api_admin_apartment_bed_list",
     *     "api_admin_apartment_bed_get",
     *     "api_admin_apartment_room_grid",
     *     "api_admin_apartment_room_list",
     *     "api_admin_apartment_room_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $number;

    /**
     * @var ApartmentRoom
     * @Assert\NotNull(
     *     message = "Please select an Apartment Room",
     *     groups={
     *          "api_admin_apartment_bed_add",
     *          "api_admin_apartment_bed_edit",
     *          "api_admin_apartment_room_add",
     *          "api_admin_apartment_room_edit"
     *     }
     * )
     * @ORM\ManyToOne(targetEntity="App\Entity\ApartmentRoom", inversedBy="beds", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_apartment_room", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_apartment_bed_grid",
     *     "api_admin_apartment_bed_list",
     *     "api_admin_apartment_bed_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_get_active",
     *     "api_admin_contract_get",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $room;

    /**
     * @var Resident
     * @Groups({
     *     "api_admin_apartment_bed_list",
     *     "api_admin_apartment_bed_get",
     *     "api_admin_apartment_room_list",
     *     "api_admin_apartment_room_get"
     * })
     */
    private $resident;

    /**
     * @var bool
     * @ORM\Column(name="enabled", type="boolean", options={"default" = 1})
     * @Assert\NotNull(groups={
     *          "api_admin_apartment_bed_add",
     *          "api_admin_apartment_bed_edit",
     *          "api_admin_apartment_room_add",
     *          "api_admin_apartment_room_edit"
     * })
     * @Groups({
     *     "api_admin_apartment_bed_list",
     *     "api_admin_apartment_bed_get",
     *     "api_admin_apartment_room_list",
     *     "api_admin_apartment_room_get"
     * })
     */
    private $enabled;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentAdmission", mappedBy="apartmentBed", cascade={"remove", "persist"})
     */
    private $residentAdmissions;

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
     * @return ApartmentRoom|null
     */
    public function getRoom(): ?ApartmentRoom
    {
        return $this->room;
    }

    /**
     * @param ApartmentRoom|null $room
     */
    public function setRoom(?ApartmentRoom $room): void
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

    /**
     * @return ArrayCollection
     */
    public function getResidentAdmissions(): ArrayCollection
    {
        return $this->residentAdmissions;
    }

    /**
     * @param ArrayCollection $residentAdmissions
     */
    public function setResidentAdmissions(ArrayCollection $residentAdmissions): void
    {
        $this->residentAdmissions = $residentAdmissions;
    }
}
