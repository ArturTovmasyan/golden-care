<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Facility
 *
 * @ORM\Entity(repositoryClass="App\Repository\FacilityRepository")
 * @ORM\Table(name="tbl_facility")
 * @Grid(
 *     api_admin_facility_grid={
 *          {"id", "number", true, true, "f.id"},
 *          {"name", "string", true, true, "f.name"},
 *          {"description", "string", true, true, "f.description"},
 *          {"shorthand", "string", true, true, "f.shorthand"},
 *          {"phone", "string", true, true, "f.phone"},
 *          {"fax", "string", true, true, "f.fax"},
 *          {"address", "string", true, true, "f.address"},
 *          {"license", "string", true, true, "f.license"},
 *          {"capacity", "string", true, true, "f.capacity"},
 *          {"csz_str", "string", true, true, "CONCAT(csz.city, ' ', csz.stateAbbr, ', ', csz.zipMain)"},
 *          {"space", "string", true, true, "s.name"}
 *     }
 * )
 */
class Facility
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_facility_list",
     *     "api_admin_facility_get",
     *     "api_admin_dining_room_list",
     *     "api_admin_dining_room_get",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_facility_add", "api_admin_facility_edit"})
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @ORM\Column(name="name", type="string", length=100)
     * @Groups({
     *     "api_admin_facility_grid",
     *     "api_admin_facility_list",
     *     "api_admin_facility_get",
     *     "api_admin_dining_room_list",
     *     "api_admin_dining_room_get",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get"
     * })
     */
    private $name;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=1000, nullable=true)
     * @Assert\Length(
     *      max = 1000,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $description;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_facility_add", "api_admin_facility_edit"})
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Shorthand cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @ORM\Column(name="shorthand", type="string", length=100)
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $shorthand;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/(\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$)/",
     *     groups={
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit"
     * })
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     * @Groups({
     *     "api_admin_facility_grid",
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
     * })
     */
    private $phone;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/(\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$)/",
     *     groups={
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit"
     * })
     * @ORM\Column(name="fax", type="string", length=20, nullable=true)
     * @Groups({
     *     "api_admin_facility_grid",
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
     * })
     */
    private $fax;
    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_facility_add", "api_admin_facility_edit"})
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Address cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @ORM\Column(name="address", type="string", length=100)
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $address;

    /**
     * @var string $license
     * @ORM\Column(name="license", type="string", length=20, nullable=true)
     * @Assert\Length(
     *      max = 20,
     *      maxMessage = "License cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $license;

    /**
     * @var CityStateZip
     * @Assert\NotNull(message = "Please select a City State & Zip", groups={"api_admin_facility_add", "api_admin_facility_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\CityStateZip")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_csz", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $csz;

    /**
     * @var int
     * @Assert\NotBlank(groups={"api_admin_facility_add", "api_admin_facility_edit"})
     * @Assert\Regex(
     *      pattern="/(^[1-9][0-9]*$)/",
     *      message="The value should be numeric",
     *      groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @ORM\Column(name="capacity", type="integer")
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $capacity;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={"api_admin_facility_add", "api_admin_facility_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\FacilityRoom", mappedBy="facility", cascade={"remove", "persist"})
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $rooms;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getShorthand(): ?string
    {
        return $this->shorthand;
    }

    public function setShorthand(string $shorthand): self
    {
        $this->shorthand = $shorthand;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(?string $fax): self
    {
        $this->fax = $fax;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function setLicense(?string $license): self
    {
        $this->license = $license;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity($capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getCsz(): ?CityStateZip
    {
        return $this->csz;
    }

    public function setCsz(?CityStateZip $csz): self
    {
        $this->csz = $csz;

        return $this;
    }

    public function getSpace(): ?Space
    {
        return $this->space;
    }

    public function setSpace(?Space $space): self
    {
        $this->space = $space;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRooms(): ArrayCollection
    {
        return $this->rooms;
    }

    /**
     * @param ArrayCollection $rooms
     */
    public function setRooms(ArrayCollection $rooms): void
    {
        $this->rooms = $rooms;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("occupation")
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    public function getOccupation()
    {
        $occupation = 0;
        if ($this->rooms !== null) {
            /** @var FacilityRoom $room */
            foreach ($this->rooms as $room) {
                $occupation += $room->getBeds()->count();
            }
        }

        return $occupation;
    }
}
