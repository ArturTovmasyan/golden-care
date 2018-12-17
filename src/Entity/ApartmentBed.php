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
 * Class ApartmentBed
 *
 * @ORM\Entity(repositoryClass="App\Repository\ApartmentBedRepository")
 * @UniqueEntity(
 *     fields={"room", "number"},
 *     errorPath="number",
 *     message="This number is already in use on that room",
 *     groups={
 *          "api_admin_apartment_bed_add",
 *          "api_admin_apartment_bed_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_apartment_bed")
 * @Grid(
 *     api_admin_apartment_bed_grid={
 *          {"id", "number", true, true, "ab.id"},
 *          {"number", "string", true, true, "ab.number"},
 *          {"room", "string", true, true, "ar.name"},
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
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active"
     * })
     */
    private $number;

    /**
     * @var ApartmentRoom
     * @Assert\NotNull(
     *     message = "Please select a ApartmentRoom",
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
     * @Groups({"api_admin_apartment_bed_grid", "api_admin_apartment_bed_list", "api_admin_apartment_bed_get"})
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
     * @return ApartmentRoom|null
     */
    public function getRoom(): ?ApartmentRoom
    {
        return $this->room;
    }

    /**
     * @param ApartmentRoom|null $room
     * @return ApartmentBed
     */
    public function setRoom(?ApartmentRoom $room): self
    {
        $this->room = $room;

        return $this;
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
}
