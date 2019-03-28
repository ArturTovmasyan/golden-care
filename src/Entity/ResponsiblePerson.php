<?php

namespace App\Entity;

use App\Model\Persistence\Entity\PhoneTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_responsible_person")
 * @ORM\Entity(repositoryClass="App\Repository\ResponsiblePersonRepository")
 * @Grid(
 *     api_admin_responsible_person_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rp.id"
 *          },
 *          {
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(sal.title,''), ' ', COALESCE(rp.firstName, ''), ' ', COALESCE(rp.middleName, ''), ' ', COALESCE(rp.lastName, ''))",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "address_1",
 *              "type"       = "string",
 *              "field"      = "rp.address_1"
 *          },
 *          {
 *              "id"         = "address_2",
 *              "type"       = "string",
 *              "field"      = "rp.address_2"
 *          },
 *          {
 *              "id"         = "email",
 *              "type"       = "string",
 *              "field"      = "rp.email"
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
class ResponsiblePerson
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_list_by_space",
     *     "api_admin_responsible_person_get",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=40, nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_admin_responsible_person_edit",
     *     "api_admin_responsible_person_add"
     * })
     * @Groups({
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_list_by_space",
     *     "api_admin_responsible_person_get",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=40, nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_admin_responsible_person_edit",
     *     "api_admin_responsible_person_add"
     * })
     * @Groups({
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_list_by_space",
     *     "api_admin_responsible_person_get",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $lastName;

    /**
     * @var string
     * @ORM\Column(name="middle_name", type="string", length=40, nullable=true)
     * @Groups({
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_list_by_space",
     *     "api_admin_responsible_person_get",
     *     "api_admin_resident_responsible_person_list"
     * })
     */
    private $middleName;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_responsible_person_edit",
     *     "api_admin_responsible_person_add"
     * })
     * @ORM\Column(name="address_1", type="string", length=100, nullable=false)
     * @Groups({
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_list_by_space",
     *     "api_admin_responsible_person_get",
     *     "api_admin_resident_responsible_person_list"
     * })
     */
    private $address_1;

    /**
     * @var string
     *
     * @ORM\Column(name="address_2", type="string", length=100, nullable=true)
     * @Groups({
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_list_by_space",
     *     "api_admin_responsible_person_get",
     *     "api_admin_resident_responsible_person_list"
     * })
     */
    private $address_2;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Groups({
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_list_by_space",
     *     "api_admin_responsible_person_get",
     *     "api_admin_resident_responsible_person_list"
     * })
     * @Assert\Email(
     *     groups={
     *          "api_admin_responsible_person_edit",
     *          "api_admin_responsible_person_add"
     *     }
     * )
     */
    private $email;

    /**
     * @var CityStateZip
     * @ORM\ManyToOne(targetEntity="CityStateZip", inversedBy="responsiblePersons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_csz", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *      "api_admin_responsible_person_edit",
     *      "api_admin_responsible_person_add"
     * })
     * @Groups({
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_list_by_space",
     *     "api_admin_responsible_person_get",
     *     "api_admin_resident_responsible_person_list"
     * })
     */
    private $csz;

    /**
     * @var Space
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="responsiblePersons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *     message = "Please select a Space",
     *     groups={
     *          "api_admin_responsible_person_add",
     *          "api_responsible_person_edit"
     *     }
     * )
     * @Groups({
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_list_by_space",
     *     "api_admin_responsible_person_get"
     * })
     */
    private $space;

    /**
     * @ORM\ManyToOne(targetEntity="Salutation", inversedBy="responsiblePersons", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_salutation", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_responsible_person_add",
     *     "api_admin_responsible_person_edit"
     * })
     * @Groups({
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_list_by_space",
     *     "api_admin_responsible_person_get",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $salutation;

    /**
     * @ORM\OneToMany(targetEntity="ResponsiblePersonPhone", mappedBy="responsiblePerson")
     * @Assert\Valid(groups={
     *     "api_admin_responsible_person_add",
     *     "api_admin_responsible_person_edit"
     * })
     * @Groups({
     *      "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_list_by_space",
     *      "api_admin_responsible_person_get",
     *     "api_admin_resident_responsible_person_list"
     * })
     */
    private $phones;

//    /**
//     * @var ArrayCollection
//     * @ORM\ManyToMany(targetEntity="App\Entity\ResidentEvent", mappedBy="responsiblePerson", cascade={"remove", "persist"})
//     */
//    private $residentEvents;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="ResidentEvent", inversedBy="responsiblePersons", cascade={"persist", "remove"})
     * @ORM\JoinTable(
     *      name="tbl_resident_event_responsible_persons",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_responsible_person", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_resident_event", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     */
    private $residentEvents;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentResponsiblePerson", mappedBy="responsiblePerson", cascade={"remove", "persist"})
     */
    private $residentResponsiblePersons;

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
    public function getAddress1(): string
    {
        return $this->address_1;
    }

    /**
     * @param string $address1
     */
    public function setAddress1(string $address1): void
    {
        $this->address_1 = $address1;
    }

    /**
     * @return string
     */
    public function getAddress2(): string
    {
        return $this->address_2;
    }

    /**
     * @param string $address2
     */
    public function setAddress2(string $address2): void
    {
        $this->address_2 = $address2;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return CityStateZip
     */
    public function getCsz(): CityStateZip
    {
        return $this->csz;
    }

    /**
     * @param CityStateZip $csz
     */
    public function setCsz(CityStateZip $csz): void
    {
        $this->csz = $csz;
    }

    /**
     * @return Space
     */
    public function getSpace(): Space
    {
        return $this->space;
    }

    /**
     * @param Space $space
     */
    public function setSpace(Space $space): void
    {
        $this->space = $space;
    }

    /**
     * @return mixed
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * @param mixed $salutation
     */
    public function setSalutation($salutation): void
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
     * @return mixed
     */
    public function getResidentEvents()
    {
        return $this->residentEvents;
    }

    /**
     * @param mixed $residentEvents
     */
    public function setResidentEvents($residentEvents): void
    {
        $this->residentEvents = $residentEvents;

        $residentEvents->addResidentEvent($this);
    }

    /**
     * @param ResidentEvent $residentEvent
     */
    public function addResidentEvent(ResidentEvent $residentEvent)
    {
        $this->residentEvents->add($residentEvent);
    }

    /**
     * @param ResidentEvent $residentEvent
     */
    public function removeResidentEvent(ResidentEvent $residentEvent)
    {
        $this->residentEvents->removeElement($residentEvent);
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentResponsiblePersons(): ArrayCollection
    {
        return $this->residentResponsiblePersons;
    }

    /**
     * @param ArrayCollection $residentResponsiblePersons
     */
    public function setResidentResponsiblePersons(ArrayCollection $residentResponsiblePersons): void
    {
        $this->residentResponsiblePersons = $residentResponsiblePersons;
    }
}
