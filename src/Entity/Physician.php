<?php
namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Annotation\Grid as Grid;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="tbl_physician")
 * @ORM\Entity(repositoryClass="App\Repository\PhysicianRepository")
 * @Grid(
 *     api_admin_physician_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "p.id"
 *          },
 *          {
 *              "id"         = "speciality",
 *              "type"       = "string",
 *              "field"      = "sp.title"
 *          },
 *          {
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(sal.title,''), ' ', COALESCE(p.firstName, ''), ' ', COALESCE(p.middleName, ''), ' ', COALESCE(p.lastName, ''))",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "address_1",
 *              "type"       = "string",
 *              "field"      = "p.address_1"
 *          },
 *          {
 *              "id"         = "address_2",
 *              "type"       = "string",
 *              "field"      = "p.address_2"
 *          },
 *          {
 *              "id"         = "email",
 *              "type"       = "string",
 *              "field"      = "p.email"
 *          },
 *          {
 *              "id"         = "website_url",
 *              "type"       = "string",
 *              "field"      = "p.websiteUrl"
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
class Physician
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups({
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_resident_grid",
     *     "api_admin_resident_list",
     *     "api_admin_resident_get",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get",
     *     "api_admin_physician_speciality_list",
     *     "api_admin_physician_speciality_get",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Space", inversedBy="physicians", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @Groups({
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     */
    private $space;

    /**
     * @var Salutation
     * @ORM\ManyToOne(targetEntity="Salutation", inversedBy="physicians")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_salutation", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @Groups({
     *      "api_admin_physician_list",
     *      "api_admin_physician_get",
     *      "api_admin_resident_event_list",
     *      "api_admin_resident_event_get",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $salutation;

    /**
     * @var CityStateZip
     * @ORM\ManyToOne(targetEntity="CityStateZip", inversedBy="physicians")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_csz", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @Groups({
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $csz;

    /**
     * @var Speciality
     * @ORM\ManyToOne(targetEntity="Speciality", inversedBy="physicians", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_speciality", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a Speciality", groups={
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @Assert\Valid(groups={
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @Groups({
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $speciality;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=60)
     * @Assert\NotBlank(groups={
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "First Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_physician_add",
     *          "api_admin_physician_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get",
     *     "api_admin_physician_speciality_list",
     *     "api_admin_physician_speciality_get",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=60)
     * @Assert\NotBlank(groups={
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "Last Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_physician_add",
     *          "api_admin_physician_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get",
     *     "api_admin_physician_speciality_list",
     *     "api_admin_physician_speciality_get",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $lastName;

    /**
     * @var string
     * @ORM\Column(name="middle_name", type="string", length=60, nullable=true)
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "Middle Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_physician_add",
     *          "api_admin_physician_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $middleName;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Address cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_physician_add",
     *          "api_admin_physician_edit"
     *      }
     * )
     * @ORM\Column(name="address_1", type="string", length=100)
     * @Groups({
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $address_1;

    /**
     * @var string
     *
     * @ORM\Column(name="address_2", type="string", length=100, nullable=true)
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Address (optional) cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_physician_add",
     *          "api_admin_physician_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $address_2;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Assert\Email(
     *     groups={
     *          "api_admin_physician_add",
     *          "api_admin_physician_edit"
     *     }
     * )
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Email cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_physician_add",
     *          "api_admin_physician_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="website_url", type="string", length=255, nullable=true)
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Website URL cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_physician_add",
     *          "api_admin_physician_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $websiteUrl;

    /**
     * @ORM\OneToMany(targetEntity="PhysicianPhone", mappedBy="physician")
     * @Assert\Valid(groups={
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @Groups({
     *      "api_admin_physician_list",
     *      "api_admin_physician_get",
     *      "api_admin_resident_physician_list"
     * })
     */
    private $phones;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentEvent", mappedBy="physician", cascade={"remove", "persist"})
     */
    private $residentEvents;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentMedication", mappedBy="physician", cascade={"remove", "persist"})
     */
    private $residentMedications;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentPhysician", mappedBy="physician", cascade={"remove", "persist"})
     */
    private $residentPhysicians;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSpace()
    {
        return $this->space;
    }

    /**
     * @param mixed $space
     */
    public function setSpace($space): void
    {
        $this->space = $space;
    }

    /**
     * @return Salutation
     */
    public function getSalutation(): Salutation
    {
        return $this->salutation;
    }

    /**
     * @param Salutation $salutation
     */
    public function setSalutation(Salutation $salutation): void
    {
        $this->salutation = $salutation;
    }

    /**
     * @return mixed
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param mixed $phones
     */
    public function setPhones($phones): void
    {
        $this->phones = $phones;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    /**
     * @param string $middleName
     */
    public function setMiddleName(string $middleName): void
    {
        $this->middleName = $middleName;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address_1;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address_2;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getWebsiteUrl()
    {
        return $this->websiteUrl;
    }

    /**
     * @return CityStateZip
     */
    public function getCsz()
    {
        return $this->csz;
    }

    /**
     * @return Speciality
     */
    public function getSpeciality(): Speciality
    {
        return $this->speciality;
    }

    /**
     * @param Speciality $speciality
     */
    public function setSpeciality(Speciality $speciality): void
    {
        $this->speciality = $speciality;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $address_1
     */
    public function setAddress1($address_1)
    {
        $this->address_1 = $address_1;
    }

    /**
     * @param string $address_2
     */
    public function setAddress2($address_2)
    {
        $this->address_2 = $address_2;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param string $websiteUrl
     */
    public function setWebsiteUrl($websiteUrl)
    {
        $this->websiteUrl = $websiteUrl;
    }

    /**
     * @param CityStateZip $csz
     */
    public function setCsz($csz)
    {
        $this->csz = $csz;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentEvents(): ArrayCollection
    {
        return $this->residentEvents;
    }

    /**
     * @param ArrayCollection $residentEvents
     */
    public function setResidentEvents(ArrayCollection $residentEvents): void
    {
        $this->residentEvents = $residentEvents;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentMedications(): ArrayCollection
    {
        return $this->residentMedications;
    }

    /**
     * @param ArrayCollection $residentMedications
     */
    public function setResidentMedications(ArrayCollection $residentMedications): void
    {
        $this->residentMedications = $residentMedications;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentPhysicians(): ArrayCollection
    {
        return $this->residentPhysicians;
    }

    /**
     * @param ArrayCollection $residentPhysicians
     */
    public function setResidentPhysicians(ArrayCollection $residentPhysicians): void
    {
        $this->residentPhysicians = $residentPhysicians;
    }
}
