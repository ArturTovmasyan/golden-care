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
 * Class Apartment
 *
 * @ORM\Entity(repositoryClass="App\Repository\ApartmentRepository")
 * @ORM\Table(name="tbl_apartment")
 * @Grid(
 *     api_admin_apartment_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "a.id"
 *          },
 *          {
 *              "id"         = "name",
 *              "type"       = "string",
 *              "field"      = "a.name",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(a.description, 1, 100)), CASE WHEN LENGTH(a.description) > 100 THEN '…' ELSE '' END)"
 *          },
 *          {
 *              "id"         = "shorthand",
 *              "type"       = "string",
 *              "field"      = "a.shorthand"
 *          },
 *          {
 *              "id"         = "phone",
 *              "type"       = "string",
 *              "field"      = "a.phone"
 *          },
 *          {
 *              "id"         = "fax",
 *              "type"       = "string",
 *              "field"      = "a.fax"
 *          },
 *          {
 *              "id"         = "address",
 *              "type"       = "string",
 *              "field"      = "a.address"
 *          },
 *          {
 *              "id"         = "license",
 *              "type"       = "string",
 *              "field"      = "a.license"
 *          },
 *          {
 *              "id"         = "beds_licensed",
 *              "type"       = "string",
 *              "col_group"  = "beds",
 *              "field"      = "a.bedsLicensed"
 *          },
 *          {
 *              "id"         = "beds_target",
 *              "type"       = "string",
 *              "col_group"  = "beds",
 *              "field"      = "a.bedsTarget"
 *          },
 *          {
 *              "id"         = "beds_configured",
 *              "type"       = "number",
 *              "col_group"  = "beds",
 *              "field"      = "(SELECT COUNT(ab) FROM \App\Entity\ApartmentBed ab JOIN ab.room r JOIN r.apartment ra WHERE ra.id=a.id AND ab.enabled=1)"
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
class Apartment
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get",
     *     "api_admin_apartment_room_list",
     *     "api_admin_apartment_room_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_get_active",
     *     "api_admin_contract_get",
     *     "api_admin_notification_list",
     *     "api_admin_notification_get",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_apartment_add",
     *          "api_admin_apartment_edit"
     * })
     * @ORM\Column(name="name", type="string", length=100)
     * @Groups({
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get",
     *     "api_admin_apartment_room_list",
     *     "api_admin_apartment_room_get",
     *     "api_admin_apartment_bed_list",
     *     "api_admin_apartment_bed_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_get_active",
     *     "api_admin_contract_get",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $name;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=1000, nullable=true)
     * @Assert\Length(
     *      max = 1000,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_apartment_add",
     *          "api_admin_apartment_edit"
     * })
     * @Groups({
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get"
     * })
     */
    private $description;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Shorthand cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_apartment_add",
     *          "api_admin_apartment_edit"
     * })
     * @ORM\Column(name="shorthand", type="string", length=100)
     * @Groups({
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get",
     *     "api_admin_apartment_bed_list",
     *     "api_admin_apartment_bed_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_get_active",
     *     "api_admin_contract_get"})
     */
    private $shorthand;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\([0-9]{3}\)\s?[0-9]{3}-[0-9]{4}$/",
     *     message="Invalid phone number format. Valid format is (XXX) XXX-XXXX.",
     *     groups={
     *          "api_admin_apartment_add",
     *          "api_admin_apartment_edit"
     * })
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     * @Groups({
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get"
     * })
     */
    private $phone;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\([0-9]{3}\)\s?[0-9]{3}-[0-9]{4}$/",
     *     message="Invalid fax number format. Valid format is (XXX) XXX-XXXX.",
     *     groups={
     *          "api_admin_apartment_add",
     *          "api_admin_apartment_edit"
     * })
     * @ORM\Column(name="fax", type="string", length=20, nullable=true)
     * @Groups({
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get"
     * })
     */
    private $fax;
    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Address cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_apartment_add",
     *          "api_admin_apartment_edit"
     * })
     * @ORM\Column(name="address", type="string", length=100)
     * @Groups({
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get"
     * })
     */
    private $address;

    /**
     * @var string $license
     * @ORM\Column(name="license", type="string", length=20, nullable=true)
     * @Assert\Length(
     *      max = 20,
     *      maxMessage = "License cannot be longer than {{ limit }} characters",
     *      groups={
     *           "api_admin_apartment_add",
     *           "api_admin_apartment_edit"
     * })
     * @Groups({
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get"
     * })
     */
    private $license;

    /**
     * @var CityStateZip
     * @Assert\NotNull(message = "Please select a City, State & Zip", groups={
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\CityStateZip", inversedBy="apartments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_csz", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get"
     * })
     */
    private $csz;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit"
     * })
     * @Assert\Regex(
     *      pattern="/^[1-9][0-9]*$/",
     *      message="The value should be numeric.",
     *      groups={
     *          "api_admin_apartment_add",
     *          "api_admin_apartment_edit"
     * })
     * @ORM\Column(name="beds_licensed", type="integer")
     * @Groups({
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get"
     * })
     */
    private $bedsLicensed;
    /**
     * @var int
     * @ORM\Column(name="license_capacity", type="integer", nullable=true)
     */
    private $licenseCapacity;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit"
     * })
     * @Assert\Regex(
     *      pattern="/^[1-9][0-9]*$/",
     *      message="The value should be numeric.",
     *      groups={
     *          "api_admin_apartment_add",
     *          "api_admin_apartment_edit"
     * })
     * @ORM\Column(name="beds_target", type="integer")
     * @Groups({
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get"
     * })
     */
    private $bedsTarget;
    /**
     * @var int
     * @ORM\Column(name="capacity", type="integer", nullable=true)
     */
    private $capacity;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="apartments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get"
     * })
     */
    private $space;

    /**
     * @var int
     * @Groups({
     *     "api_admin_apartment_get"
     * })
     */
    private $bedsConfigured;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ApartmentRoom", mappedBy="apartment", cascade={"remove", "persist"})
     * @Groups({
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get"
     * })
     */
    private $rooms;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Notification", mappedBy="apartments", cascade={"persist"})
     */
    protected $notifications;

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

    public function getBedsLicensed(): ?int
    {
        return $this->bedsLicensed;
    }

    public function setBedsLicensed($bedsLicensed): void
    {
        $this->bedsLicensed = $bedsLicensed;
    }

    public function getBedsTarget(): ?int
    {
        return $this->bedsTarget;
    }

    public function setBedsTarget($bedsTarget): void
    {
        $this->bedsTarget = $bedsTarget;
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
     * @return int|null
     */
    public function getBedsConfigured(): ?int
    {
        return $this->bedsConfigured;
    }

    /**
     * @param int|null $bedsConfigured
     */
    public function setBedsConfigured(?int $bedsConfigured): void
    {
        $this->bedsConfigured = $bedsConfigured;
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
     * @return mixed
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param mixed $notifications
     */
    public function setNotifications($notifications): void
    {
        $this->notifications = $notifications;

        /** @var Notification $notification */
        foreach ($this->notifications as $notification) {
            $notification->addApartment($this);
        }
    }

    /**
     * @param Notification $notification
     */
    public function addNotification(Notification $notification): void
    {
        $notification->addApartment($this);
        $this->notifications[] = $notification;
    }

    /**
     * @param Notification $notification
     */
    public function removeNotification(Notification $notification): void
    {
        $this->notifications->removeElement($notification);
        $notification->removeApartment($this);
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("occupation")
     * @Groups({
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get"
     * })
     */
    public function getOccupation(): ?int
    {
        $occupation = 0;
        if ($this->rooms !== null) {
            /** @var ApartmentRoom $room */
            foreach ($this->rooms as $room) {
                $occupation += $room->getBeds()->count();
            }
        }

        return $occupation;
    }

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback(groups={
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit"
     * })
     */
    public function areBedsTargetValid(ExecutionContextInterface $context): void
    {
        $bedsLicensed = $this->bedsLicensed;
        $bedsTarget = $this->bedsTarget;

        if ($bedsTarget > $bedsLicensed) {
            $context->buildViolation('The Beds Target "' . $bedsTarget . '" should be less than or equal to Beds Licensed "' . $bedsLicensed . '".')
                ->atPath('bedsTarget')
                ->addViolation();
        }
    }
}
