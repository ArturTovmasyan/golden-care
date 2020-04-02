<?php

namespace App\Entity;

use App\Entity\Lead\Lead;
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
 *              "field"      = "f.name",
 *              "link"       = "/facility/:id"
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
 *              "id"         = "csz_str",
 *              "type"       = "string",
 *              "field"      = "CONCAT(csz.city, ' ', csz.stateAbbr, ', ', csz.zipMain)"
 *          },
 *          {
 *              "id"         = "license",
 *              "type"       = "string",
 *              "field"      = "f.license"
 *          },
 *          {
 *              "id"         = "beds_licensed",
 *              "type"       = "string",
 *              "col_group"  = "beds",
 *              "field"      = "f.bedsLicensed"
 *          },
 *          {
 *              "id"         = "beds_target",
 *              "type"       = "string",
 *              "col_group"  = "beds",
 *              "field"      = "f.bedsTarget"
 *          },
 *          {
 *              "id"         = "beds_configured",
 *              "type"       = "number",
 *              "col_group"  = "beds",
 *              "field"      = "(SELECT COUNT(fb) FROM \App\Entity\FacilityBed fb JOIN fb.room r JOIN r.facility rf WHERE rf.id=f.id AND fb.enabled=1)"
 *          },
 *          {
 *              "id"         = "red_flag",
 *              "type"       = "string",
 *              "field"      = "f.redFlag"
 *          },
 *          {
 *              "id"         = "yellow_flag",
 *              "type"       = "string",
 *              "field"      = "f.yellowFlag"
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
     *     "api_admin_facility_room_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_get_active",
     *     "api_admin_contract_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     *     "api_admin_resident_get_last_admission",
     *     "api_admin_document_list",
     *     "api_admin_document_get",
     *     "api_admin_notification_list",
     *     "api_admin_notification_get",
     *     "api_admin_facility_document_list",
     *     "api_admin_facility_document_get",
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get",
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get",
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get",
     *     "api_admin_facility_room_type_list",
     *     "api_admin_facility_room_type_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit"
     * })
     * @ORM\Column(name="name", type="string", length=100)
     * @Groups({
     *     "api_admin_facility_list",
     *     "api_admin_facility_get",
     *     "api_admin_dining_room_list",
     *     "api_admin_dining_room_get",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get",
     *     "api_admin_facility_bed_list",
     *     "api_admin_facility_bed_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_get_active",
     *     "api_admin_contract_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     *     "api_admin_resident_get_last_admission",
     *     "api_admin_facility_document_list",
     *     "api_admin_facility_document_get",
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get",
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get",
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get",
     *     "api_admin_facility_room_type_list",
     *     "api_admin_facility_room_type_get"
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
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit"
     * })
     * @Groups({
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
     * })
     */
    private $description;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Shorthand cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit"
     * })
     * @ORM\Column(name="shorthand", type="string", length=100)
     * @Groups({
     *     "api_admin_facility_list",
     *     "api_admin_facility_get",
     *     "api_admin_facility_bed_list",
     *     "api_admin_facility_bed_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_get_active",
     *     "api_admin_contract_get"
     * })
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
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
     * })
     */
    private $fax;
    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Address cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit"
     * })
     * @ORM\Column(name="address", type="string", length=100)
     * @Groups({
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
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
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit"
     * })
     * @Groups({
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
     * })
     */
    private $license;

    /**
     * @var CityStateZip
     * @Assert\NotNull(message = "Please select a City, State & Zip", groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\CityStateZip", inversedBy="facilities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_csz", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
     * })
     */
    private $csz;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit"
     * })
     * @Assert\Regex(
     *      pattern="/^[1-9][0-9]*$/",
     *      message="The value should be numeric.",
     *      groups={
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit"
     * })
     * @ORM\Column(name="beds_licensed", type="integer")
     * @Groups({
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
     * })
     */
    private $bedsLicensed;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit"
     * })
     * @Assert\Regex(
     *      pattern="/^[1-9][0-9]*$/",
     *      message="The value should be numeric.",
     *      groups={
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit"
     * })
     * @ORM\Column(name="beds_target", type="integer")
     * @Groups({
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
     * })
     */
    private $bedsTarget;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^[1-9]?$)/",
     *      message="The value can take numbers from 1 to 9.",
     *      groups={
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit"
     * })
     * @ORM\Column(name="number_of_floors", type="integer", length=1)
     * @Groups({
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
     * })
     */
    private $numberOfFloors = 1;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit"
     * })
     * @Assert\Regex(
     *      pattern="/^[1-9][0-9]*$/",
     *      message="The value should be numeric.",
     *      groups={
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit"
     * })
     * @ORM\Column(name="red_flag", type="integer")
     * @Groups({
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
     * })
     */
    private $redFlag;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit"
     * })
     * @Assert\Regex(
     *      pattern="/^[1-9][0-9]*$/",
     *      message="The value should be numeric.",
     *      groups={
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit"
     * })
     * @ORM\Column(name="yellow_flag", type="integer")
     * @Groups({
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
     * })
     */
    private $yellowFlag;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="facilities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
     * })
     */
    private $space;

    /**
     * @var int
     * @Groups({
     *     "api_admin_facility_get"
     * })
     */
    private $bedsConfigured;

    /**
     * @var array $potentialNames
     * @ORM\Column(name="potential_names", type="json_array", nullable=true)
     */
    private $potentialNames = [];

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\FacilityRoom", mappedBy="facility", cascade={"remove", "persist"})
     * @Groups({
     *     "api_admin_facility_grid",
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
     * })
     */
    private $rooms;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\DiningRoom", mappedBy="facility", cascade={"remove", "persist"})
     */
    private $diningRooms;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Activity", mappedBy="facility", cascade={"remove", "persist"})
     */
    private $leadActivities;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Lead", mappedBy="primaryFacility", cascade={"remove", "persist"})
     */
    private $leads;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Lead\Lead", mappedBy="facilities", cascade={"persist"})
     */
    protected $facilityLeads;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Notification", mappedBy="facilities", cascade={"persist"})
     */
    protected $notifications;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Document", mappedBy="facilities", cascade={"persist"})
     */
    protected $documents;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\FacilityDocument", mappedBy="facility", cascade={"remove", "persist"})
     */
    private $facilityDocuments;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\FacilityDashboard", mappedBy="facility", cascade={"remove", "persist"})
     */
    private $dashboards;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\FacilityEvent", mappedBy="facility", cascade={"remove", "persist"})
     */
    private $facilityEvents;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\CorporateEvent", mappedBy="facilities", cascade={"persist"})
     */
    protected $corporateEvents;

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

    public function getNumberOfFloors(): ?int
    {
        return $this->numberOfFloors;
    }

    public function setNumberOfFloors($numberOfFloors): void
    {
        $this->numberOfFloors = $numberOfFloors;
    }

    public function getCsz(): ?CityStateZip
    {
        return $this->csz;
    }

    public function setCsz(?CityStateZip $csz): void
    {
        $this->csz = $csz;
    }

    /**
     * @return int|null
     */
    public function getRedFlag(): ?int
    {
        return $this->redFlag;
    }

    /**
     * @param int|null $redFlag
     */
    public function setRedFlag(?int $redFlag): void
    {
        $this->redFlag = $redFlag;
    }

    /**
     * @return int|null
     */
    public function getYellowFlag(): ?int
    {
        return $this->yellowFlag;
    }

    /**
     * @param int|null $yellowFlag
     */
    public function setYellowFlag(?int $yellowFlag): void
    {
        $this->yellowFlag = $yellowFlag;
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
     * @return array
     */
    public function getPotentialNames(): array
    {
        return $this->potentialNames;
    }

    /**
     * @param array $potentialNames
     */
    public function setPotentialNames(array $potentialNames): void
    {
        $this->potentialNames = $potentialNames;
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
     * @return ArrayCollection
     */
    public function getDiningRooms(): ArrayCollection
    {
        return $this->diningRooms;
    }

    /**
     * @param ArrayCollection $diningRooms
     */
    public function setDiningRooms(ArrayCollection $diningRooms): void
    {
        $this->diningRooms = $diningRooms;
    }

    /**
     * @return ArrayCollection
     */
    public function getLeadActivities(): ArrayCollection
    {
        return $this->leadActivities;
    }

    /**
     * @param ArrayCollection $leadActivities
     */
    public function setLeadActivities(ArrayCollection $leadActivities): void
    {
        $this->leadActivities = $leadActivities;
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

    /**
     * @return mixed
     */
    public function getFacilityLeads()
    {
        return $this->facilityLeads;
    }

    /**
     * @param mixed $facilityLeads
     */
    public function setFacilityLeads($facilityLeads): void
    {
        $this->facilityLeads = $facilityLeads;

        /** @var Lead $lead */
        foreach ($this->facilityLeads as $lead) {
            $lead->addFacility($this);
        }
    }

    /**
     * @param Lead $lead
     */
    public function addLead(Lead $lead): void
    {
        $lead->addFacility($this);
        $this->facilityLeads[] = $lead;
    }

    /**
     * @param Lead $lead
     */
    public function removeLead(Lead $lead): void
    {
        $this->facilityLeads->removeElement($lead);
        $lead->removeFacility($this);
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
            $notification->addFacility($this);
        }
    }

    /**
     * @param Notification $notification
     */
    public function addNotification(Notification $notification): void
    {
        $notification->addFacility($this);
        $this->notifications[] = $notification;
    }

    /**
     * @param Notification $notification
     */
    public function removeNotification(Notification $notification): void
    {
        $this->notifications->removeElement($notification);
        $notification->removeFacility($this);
    }

    /**
     * @return mixed
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param mixed $documents
     */
    public function setDocuments($documents): void
    {
        $this->documents = $documents;

        /** @var Document $document */
        foreach ($this->documents as $document) {
            $document->addFacility($this);
        }
    }

    /**
     * @param Document $document
     */
    public function addDocument(Document $document): void
    {
        $document->addFacility($this);
        $this->documents[] = $document;
    }

    /**
     * @param Document $document
     */
    public function removeDocument(Document $document): void
    {
        $this->documents->removeElement($document);
        $document->removeFacility($this);
    }

    /**
     * @return ArrayCollection
     */
    public function getFacilityDocuments(): ArrayCollection
    {
        return $this->facilityDocuments;
    }

    /**
     * @param ArrayCollection $facilityDocuments
     */
    public function setFacilityDocuments(ArrayCollection $facilityDocuments): void
    {
        $this->facilityDocuments = $facilityDocuments;
    }

    /**
     * @return ArrayCollection
     */
    public function getDashboards(): ArrayCollection
    {
        return $this->dashboards;
    }

    /**
     * @param ArrayCollection $dashboards
     */
    public function setDashboards(ArrayCollection $dashboards): void
    {
        $this->dashboards = $dashboards;
    }

    /**
     * @return ArrayCollection
     */
    public function getFacilityEvents(): ArrayCollection
    {
        return $this->facilityEvents;
    }

    /**
     * @param ArrayCollection $facilityEvents
     */
    public function setFacilityEvents(ArrayCollection $facilityEvents): void
    {
        $this->facilityEvents = $facilityEvents;
    }

    /**
     * @return mixed
     */
    public function getCorporateEvents()
    {
        return $this->corporateEvents;
    }

    /**
     * @param mixed $corporateEvents
     */
    public function setCorporateEvents($corporateEvents): void
    {
        $this->corporateEvents = $corporateEvents;

        /** @var CorporateEvent $corporateEvent */
        foreach ($this->corporateEvents as $corporateEvent) {
            $corporateEvent->addFacility($this);
        }
    }

    /**
     * @param CorporateEvent $corporateEvent
     */
    public function addCorporateEvent(CorporateEvent $corporateEvent): void
    {
        $corporateEvent->addFacility($this);
        $this->corporateEvents[] = $corporateEvent;
    }

    /**
     * @param CorporateEvent $corporateEvent
     */
    public function removeCorporateEvent(CorporateEvent $corporateEvent): void
    {
        $this->corporateEvents->removeElement($corporateEvent);
        $corporateEvent->removeFacility($this);
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("occupation")
     * @Groups({
     *     "api_admin_facility_list",
     *     "api_admin_facility_get"
     * })
     */
    public function getOccupation(): ?int
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
     * @Assert\Callback(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit"
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

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit"
     * })
     */
    public function areRedFlagValid(ExecutionContextInterface $context): void
    {
        $yellowFlag = $this->yellowFlag;
        $redFlag = $this->redFlag;

        if ($redFlag >= $yellowFlag) {
            $context->buildViolation('The Red Flag "' . $redFlag . '" should be less than Yellow Flag "' . $yellowFlag . '".')
                ->atPath('redFlag')
                ->addViolation();
        }
    }
}
