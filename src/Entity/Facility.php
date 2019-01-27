<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
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
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "f.id"
 *          },
 *          {
 *              "id"         = "name",
 *              "type"       = "string",
 *              "field"      = "f.name"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "f.description"
 *          },
 *          {
 *              "id"         = "shorthand",
 *              "type"       = "string",
 *              "field"      = "f.shorthand"
 *          },
 *          {
 *              "id"         = "phone",
 *              "type"       = "string",
 *              "field"      = "f.phone"
 *          },
 *          {
 *              "id"         = "fax",
 *              "type"       = "string",
 *              "field"      = "f.fax"
 *          },
 *          {
 *              "id"         = "address",
 *              "type"       = "string",
 *              "field"      = "f.address"
 *          },
 *          {
 *              "id"         = "license",
 *              "type"       = "string",
 *              "field"      = "f.license"
 *          },
 *          {
 *              "id"         = "license_capacity",
 *              "type"       = "string",
 *              "field"      = "f.licenseCapacity"
 *          },
 *          {
 *              "id"         = "capacity",
 *              "type"       = "string",
 *              "field"      = "f.capacity"
 *          },
 *          {
 *              "id"         = "csz_str",
 *              "type"       = "string",
 *              "field"      = "CONCAT(csz.city, ' ', csz.stateAbbr, ', ', csz.zipMain)"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
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
     *     pattern="/^\([0-9]{3}\)\s?[0-9]{3}-[0-9]{4}$/",
     *     message="Invalid phone number format. Valid format is (XXX) XXX-XXXX.",
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
     *     pattern="/^\([0-9]{3}\)\s?[0-9]{3}-[0-9]{4}$/",
     *     message="Invalid fax number format. Valid format is (XXX) XXX-XXXX.",
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
     * @ORM\Column(name="license_capacity", type="integer")
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $licenseCapacity;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getShorthand(): ?string
    {
        return $this->shorthand;
    }

    public function setShorthand(string $shorthand): void
    {
        $this->shorthand = $shorthand;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(?string $fax): void
    {
        $this->fax = $fax;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function setLicense(?string $license): void
    {
        $this->license = $license;
    }

    public function getLicenseCapacity(): ?int
    {
        return $this->licenseCapacity;
    }

    public function setLicenseCapacity($licenseCapacity): void
    {
        $this->licenseCapacity = $licenseCapacity;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity($capacity): void
    {
        $this->capacity = $capacity;
    }

    public function getCsz(): ?CityStateZip
    {
        return $this->csz;
    }

    public function setCsz(?CityStateZip $csz): void
    {
        $this->csz = $csz;
    }

    public function getSpace(): ?Space
    {
        return $this->space;
    }

    public function setSpace(?Space $space): void
    {
        $this->space = $space;
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

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback(groups={"api_admin_facility_add", "api_admin_facility_edit"})
     */
    public function areCapacityValid(ExecutionContextInterface $context): void
    {
        $licenseCapacity = $this->getLicenseCapacity();
        $capacity = $this->getCapacity();

        if ($capacity > $licenseCapacity) {
            $context->buildViolation('The capacity "'.$capacity.'" should be less than license capacity "'.$licenseCapacity.'".')
                ->atPath('capacity')
                ->addViolation();
        }
    }
}
