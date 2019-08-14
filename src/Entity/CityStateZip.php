<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class CityStateZip
 *
 * @ORM\Entity(repositoryClass="App\Repository\CityStateZipRepository")
 * @ORM\Table(name="tbl_city_state_zip")
 * @Grid(
 *     api_admin_city_state_zip_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "csz.id"
 *          },
 *          {
 *              "id"         = "state_full",
 *              "type"       = "string",
 *              "field"      = "csz.stateFull"
 *          },
 *          {
 *              "id"         = "state_abbr",
 *              "type"       = "string",
 *              "field"      = "csz.stateAbbr"
 *          },
 *          {
 *              "id"         = "zip_main",
 *              "type"       = "string",
 *              "field"      = "csz.zipMain"
 *          },
 *          {
 *              "id"         = "zip_sub",
 *              "type"       = "string",
 *              "field"      = "csz.zipSub"
 *          },
 *          {
 *              "id"         = "city",
 *              "type"       = "string",
 *              "field"      = "csz.city"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class CityStateZip
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_city_state_zip_list",
     *     "api_admin_city_state_zip_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_facility_list",
     *     "api_admin_facility_get",
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get",
     *     "api_lead_organization_list",
     *     "api_lead_organization_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_city_state_zip_add",
     *     "api_admin_city_state_zip_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "State Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_city_state_zip_add",
     *          "api_admin_city_state_zip_edit"
     * })
     * @ORM\Column(name="state_full", type="string", length=100)
     * @Groups({
     *     "api_admin_city_state_zip_list",
     *     "api_admin_city_state_zip_get",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $stateFull;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_city_state_zip_add",
     *     "api_admin_city_state_zip_edit"
     * })
     * @Assert\Regex(
     *     pattern="/^[A-Z]{2}$/",
     *     message="Invalid State abbreviation.",
     *     groups={
     *         "api_admin_city_state_zip_add",
     *         "api_admin_city_state_zip_edit"
     * })
     * @ORM\Column(name="state_abbr", type="string", length=2)
     * @Groups({
     *     "api_admin_city_state_zip_list",
     *     "api_admin_city_state_zip_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_facility_list",
     *     "api_admin_facility_get",
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_get_active",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get",
     *     "api_lead_organization_list",
     *     "api_lead_organization_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $stateAbbr;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_city_state_zip_add",
     *     "api_admin_city_state_zip_edit"
     * })
     * @Assert\Regex(
     *     pattern="/^[0-9]{5}([- ]?[0-9]{4})?$/",
     *     message="Invalid ZIP code.",
     *     groups={
     *         "api_admin_city_state_zip_add",
     *         "api_admin_city_state_zip_edit"
     * })
     * @ORM\Column(name="zip_main", type="string", length=10)
     * @Groups({
     *     "api_admin_city_state_zip_list",
     *     "api_admin_city_state_zip_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_facility_list",
     *     "api_admin_facility_get",
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_get_active",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get",
     *     "api_lead_organization_list",
     *     "api_lead_organization_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $zipMain;

    /**
     * @var string $zipSub
     * @ORM\Column(name="zip_sub", type="string", length=10, nullable=true)
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "ZIP Sub cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_city_state_zip_add",
     *          "api_admin_city_state_zip_edit"
     * })
     * @Groups({
     *     "api_admin_city_state_zip_list",
     *     "api_admin_city_state_zip_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $zipSub;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_city_state_zip_add",
     *     "api_admin_city_state_zip_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "City Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_city_state_zip_add",
     *          "api_admin_city_state_zip_edit"
     * })
     * @ORM\Column(name="city", type="string", length=100)
     * @Groups({
     *     "api_admin_city_state_zip_list",
     *     "api_admin_city_state_zip_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_facility_list",
     *     "api_admin_facility_get",
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_get_active",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get",
     *     "api_lead_organization_list",
     *     "api_lead_organization_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $city;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_city_state_zip_add",
     *     "api_admin_city_state_zip_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="cszs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_city_state_zip_list",
     *     "api_admin_city_state_zip_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Apartment", mappedBy="csz", cascade={"remove", "persist"})
     */
    private $apartments;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Facility", mappedBy="csz", cascade={"remove", "persist"})
     */
    private $facilities;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Physician", mappedBy="csz", cascade={"remove", "persist"})
     */
    private $physicians;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentAdmission", mappedBy="csz", cascade={"remove", "persist"})
     */
    private $residentAdmissions;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResponsiblePerson", mappedBy="csz", cascade={"remove", "persist"})
     */
    private $responsiblePersons;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Organization", mappedBy="csz", cascade={"remove", "persist"})
     */
    private $organizations;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Lead", mappedBy="responsiblePersonCsz", cascade={"remove", "persist"})
     */
    private $leads;

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

    public function getStateFull(): ?string
    {
        return $this->stateFull;
    }

    public function setStateFull(?string $stateFull): void
    {
        $this->stateFull = $stateFull;
    }

    public function getStateAbbr(): ?string
    {
        return $this->stateAbbr;
    }

    public function setStateAbbr(?string $stateAbbr): void
    {
        $this->stateAbbr = $stateAbbr;
    }

    /**
     * @return null|string
     */
    public function getZipMain(): ?string
    {
        return $this->zipMain;
    }

    public function setZipMain(?string $zipMain): void
    {
        $this->zipMain = $zipMain;
    }

    public function getZipSub(): ?string
    {
        return $this->zipSub;
    }

    public function setZipSub(?string $zipSub): void
    {
        $this->zipSub = $zipSub;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return Space|null
     */
    public function getSpace(): ?Space
    {
        return $this->space;
    }

    /**
     * @param Space|null $space
     */
    public function setSpace(?Space $space): void
    {
        $this->space = $space;
    }

    /**
     * @return ArrayCollection
     */
    public function getApartments(): ArrayCollection
    {
        return $this->apartments;
    }

    /**
     * @param ArrayCollection $apartments
     */
    public function setApartments(ArrayCollection $apartments): void
    {
        $this->apartments = $apartments;
    }

    /**
     * @return ArrayCollection
     */
    public function getFacilities(): ArrayCollection
    {
        return $this->facilities;
    }

    /**
     * @param ArrayCollection $facilities
     */
    public function setFacilities(ArrayCollection $facilities): void
    {
        $this->facilities = $facilities;
    }

    /**
     * @return ArrayCollection
     */
    public function getPhysicians(): ArrayCollection
    {
        return $this->physicians;
    }

    /**
     * @param ArrayCollection $physicians
     */
    public function setPhysicians(ArrayCollection $physicians): void
    {
        $this->physicians = $physicians;
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

    /**
     * @return ArrayCollection
     */
    public function getResponsiblePersons(): ArrayCollection
    {
        return $this->responsiblePersons;
    }

    /**
     * @param ArrayCollection $responsiblePersons
     */
    public function setResponsiblePersons(ArrayCollection $responsiblePersons): void
    {
        $this->responsiblePersons = $responsiblePersons;
    }

    /**
     * @return ArrayCollection
     */
    public function getOrganizations(): ArrayCollection
    {
        return $this->organizations;
    }

    /**
     * @param ArrayCollection $organizations
     */
    public function setOrganizations(ArrayCollection $organizations): void
    {
        $this->organizations = $organizations;
    }

    /**
     * @return ArrayCollection
     */
    public function getLeads(): ArrayCollection
    {
        return $this->leads;
    }

    /**
     * @param ArrayCollection $leads
     */
    public function setLeads(ArrayCollection $leads): void
    {
        $this->leads = $leads;
    }
}
