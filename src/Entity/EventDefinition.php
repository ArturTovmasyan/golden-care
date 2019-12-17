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
 * Class EventDefinition
 *
 * @ORM\Entity(repositoryClass="App\Repository\EventDefinitionRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_event_definition_add",
 *          "api_admin_event_definition_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_event_definition")
 * @Grid(
 *     api_admin_event_definition_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ed.id"
 *          },
 *          {
 *              "id"         = "type",
 *              "type"       = "enum",
 *              "field"      = "ed.type",
 *              "values"     = "\App\Model\EventDefinitionType::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "ed.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "in_chooser",
 *              "type"       = "boolean",
 *              "field"      = "ed.inChooser"
 *          },
 *          {
 *              "id"         = "ffc",
 *              "type"       = "boolean",
 *              "field"      = "ed.ffc"
 *          },
 *          {
 *              "id"         = "ihc",
 *              "type"       = "boolean",
 *              "field"      = "ed.ihc"
 *          },
 *          {
 *              "id"         = "il",
 *              "type"       = "boolean",
 *              "field"      = "ed.il"
 *          },
 *          {
 *              "id"         = "physician",
 *              "type"       = "boolean",
 *              "field"      = "ed.physician"
 *          },
 *          {
 *              "id"         = "physician_optional",
 *              "type"       = "boolean",
 *              "field"      = "ed.physicianOptional"
 *          },
 *          {
 *              "id"         = "responsible_person",
 *              "type"       = "boolean",
 *              "field"      = "ed.responsiblePerson"
 *          },
 *          {
 *              "id"         = "responsible_person_optional",
 *              "type"       = "boolean",
 *              "field"      = "ed.responsiblePersonOptional"
 *          },
 *          {
 *              "id"         = "responsible_person_multi",
 *              "type"       = "boolean",
 *              "field"      = "ed.responsiblePersonMulti"
 *          },
 *          {
 *              "id"         = "responsible_person_multi_optional",
 *              "type"       = "boolean",
 *              "field"      = "ed.responsiblePersonMultiOptional"
 *          },
 *          {
 *              "id"         = "additional_date",
 *              "type"       = "boolean",
 *              "field"      = "ed.additionalDate"
 *          },
 *          {
 *              "id"         = "resident",
 *              "type"       = "boolean",
 *              "field"      = "ed.resident"
 *          },
 *          {
 *              "id"         = "facility",
 *              "type"       = "boolean",
 *              "field"      = "ed.facility"
 *          },
 *          {
 *              "id"         = "corporate",
 *              "type"       = "boolean",
 *              "field"      = "ed.corporate"
 *          },
 *          {
 *              "id"         = "residents",
 *              "type"       = "boolean",
 *              "field"      = "ed.residents"
 *          },
 *          {
 *              "id"         = "users",
 *              "type"       = "boolean",
 *              "field"      = "ed.users"
 *          },
 *          {
 *              "id"         = "duration",
 *              "type"       = "boolean",
 *              "field"      = "ed.duration"
 *          },
 *          {
 *              "id"         = "repeats",
 *              "type"       = "boolean",
 *              "field"      = "ed.repeats"
 *          },
 *          {
 *              "id"         = "rsvp",
 *              "type"       = "boolean",
 *              "field"      = "ed.rsvp"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class EventDefinition
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get",
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    private $id;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_event_definition_add",
     *     "api_admin_event_definition_edit"
     * })
     * @Assert\Choice(
     *     callback={"App\Model\EventDefinitionType","getTypeValues"},
     *     groups={
     *         "api_admin_event_definition_add",
     *         "api_admin_event_definition_edit"
     * })
     * @ORM\Column(name="type", type="smallint")
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    private $type;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_event_definition_add",
     *     "api_admin_event_definition_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_event_definition_add",
     *          "api_admin_event_definition_edit"
     * })
     * @ORM\Column(name="title", type="string", length=100)
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get",
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_event_definition_add",
     *     "api_admin_event_definition_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="eventDefinitions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    private $space;

    /**
     * @var bool
     * @ORM\Column(name="show_in_chooser", type="boolean", options={"default" = 1})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $inChooser;

    /**
     * @var bool
     * @ORM\Column(name="show_resident_ffc", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $ffc;

    /**
     * @var bool
     * @ORM\Column(name="show_resident_ihc", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $ihc;

    /**
     * @var bool
     * @ORM\Column(name="show_resident_il", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $il;

    /**
     * @var bool
     * @ORM\Column(name="show_physician", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $physician;

    /**
     * @var bool
     * @ORM\Column(name="show_physician_optional", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $physicianOptional;

    /**
     * @var bool
     * @ORM\Column(name="show_responsible_person", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $responsiblePerson;

    /**
     * @var bool
     * @ORM\Column(name="show_responsible_person_optional", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $responsiblePersonOptional;

    /**
     * @var bool
     * @ORM\Column(name="show_responsible_person_multi", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $responsiblePersonMulti;

    /**
     * @var bool
     * @ORM\Column(name="show_responsible_person_multi_optional", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $responsiblePersonMultiOptional;

    /**
     * @var bool
     * @ORM\Column(name="show_additional_date", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $additionalDate;

    /**
     * @var bool
     * @ORM\Column(name="show_in_resident", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $resident;

    /**
     * @var bool
     * @ORM\Column(name="show_in_facility", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $facility;

    /**
     * @var bool
     * @ORM\Column(name="show_in_corporate", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $corporate;

    /**
     * @var bool
     * @ORM\Column(name="show_residents", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $residents;

    /**
     * @var bool
     * @ORM\Column(name="show_users", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $users;

    /**
     * @var bool
     * @ORM\Column(name="show_duration", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $duration;

    /**
     * @var bool
     * @ORM\Column(name="show_repeats", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $repeats;

    /**
     * @var bool
     * @ORM\Column(name="show_rsvp", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $rsvp;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentEvent", mappedBy="definition", cascade={"remove", "persist"})
     */
    private $residentEvents;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\FacilityEvent", mappedBy="definition", cascade={"remove", "persist"})
     */
    private $facilityEvents;

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
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int|null $type
     */
    public function setType(?int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = preg_replace('/\s\s+/', ' ', $title);
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
     * @return bool
     */
    public function isInChooser(): bool
    {
        return $this->inChooser;
    }

    /**
     * @param bool $inChooser
     */
    public function setInChooser(bool $inChooser): void
    {
        $this->inChooser = $inChooser;
    }

    /**
     * @return bool
     */
    public function isFfc(): bool
    {
        return $this->ffc;
    }

    /**
     * @param bool $ffc
     */
    public function setFfc(bool $ffc): void
    {
        $this->ffc = $ffc;
    }

    /**
     * @return bool
     */
    public function isIhc(): bool
    {
        return $this->ihc;
    }

    /**
     * @param bool $ihc
     */
    public function setIhc(bool $ihc): void
    {
        $this->ihc = $ihc;
    }

    /**
     * @return bool
     */
    public function isIl(): bool
    {
        return $this->il;
    }

    /**
     * @param bool $il
     */
    public function setIl(bool $il): void
    {
        $this->il = $il;
    }

    /**
     * @return bool
     */
    public function isPhysician(): bool
    {
        return $this->physician;
    }

    /**
     * @param bool $physician
     */
    public function setPhysician(bool $physician): void
    {
        $this->physician = $physician;
    }

    /**
     * @return bool
     */
    public function isResponsiblePerson(): bool
    {
        return $this->responsiblePerson;
    }

    /**
     * @param bool $responsiblePerson
     */
    public function setResponsiblePerson(bool $responsiblePerson): void
    {
        $this->responsiblePerson = $responsiblePerson;
    }

    /**
     * @return bool
     */
    public function isResponsiblePersonMulti(): bool
    {
        return $this->responsiblePersonMulti;
    }

    /**
     * @param bool $responsiblePersonMulti
     */
    public function setResponsiblePersonMulti(bool $responsiblePersonMulti): void
    {
        $this->responsiblePersonMulti = $responsiblePersonMulti;
    }

    /**
     * @return bool
     */
    public function isPhysicianOptional(): bool
    {
        return $this->physicianOptional;
    }

    /**
     * @param bool $physicianOptional
     */
    public function setPhysicianOptional(bool $physicianOptional): void
    {
        $this->physicianOptional = $physicianOptional;
    }

    /**
     * @return bool
     */
    public function isResponsiblePersonOptional(): bool
    {
        return $this->responsiblePersonOptional;
    }

    /**
     * @param bool $responsiblePersonOptional
     */
    public function setResponsiblePersonOptional(bool $responsiblePersonOptional): void
    {
        $this->responsiblePersonOptional = $responsiblePersonOptional;
    }

    /**
     * @return bool
     */
    public function isResponsiblePersonMultiOptional(): bool
    {
        return $this->responsiblePersonMultiOptional;
    }

    /**
     * @param bool $responsiblePersonMultiOptional
     */
    public function setResponsiblePersonMultiOptional(bool $responsiblePersonMultiOptional): void
    {
        $this->responsiblePersonMultiOptional = $responsiblePersonMultiOptional;
    }

    /**
     * @return bool
     */
    public function isAdditionalDate(): bool
    {
        return $this->additionalDate;
    }

    /**
     * @param bool $additionalDate
     */
    public function setAdditionalDate(bool $additionalDate): void
    {
        $this->additionalDate = $additionalDate;
    }

    /**
     * @return bool
     */
    public function isResident(): bool
    {
        return $this->resident;
    }

    /**
     * @param bool $resident
     */
    public function setResident(bool $resident): void
    {
        $this->resident = $resident;
    }

    /**
     * @return bool
     */
    public function isFacility(): bool
    {
        return $this->facility;
    }

    /**
     * @param bool $facility
     */
    public function setFacility(bool $facility): void
    {
        $this->facility = $facility;
    }

    /**
     * @return bool
     */
    public function isCorporate(): bool
    {
        return $this->corporate;
    }

    /**
     * @param bool $corporate
     */
    public function setCorporate(bool $corporate): void
    {
        $this->corporate = $corporate;
    }

    /**
     * @return bool
     */
    public function isResidents(): bool
    {
        return $this->residents;
    }

    /**
     * @param bool $residents
     */
    public function setResidents(bool $residents): void
    {
        $this->residents = $residents;
    }

    /**
     * @return bool
     */
    public function isUsers(): bool
    {
        return $this->users;
    }

    /**
     * @param bool $users
     */
    public function setUsers(bool $users): void
    {
        $this->users = $users;
    }

    /**
     * @return bool
     */
    public function isDuration(): bool
    {
        return $this->duration;
    }

    /**
     * @param bool $duration
     */
    public function setDuration(bool $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * @return bool
     */
    public function isRepeats(): bool
    {
        return $this->repeats;
    }

    /**
     * @param bool $repeats
     */
    public function setRepeats(bool $repeats): void
    {
        $this->repeats = $repeats;
    }

    /**
     * @return bool
     */
    public function isRsvp(): bool
    {
        return $this->rsvp;
    }

    /**
     * @param bool $rsvp
     */
    public function setRsvp(bool $rsvp): void
    {
        $this->rsvp = $rsvp;
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
}
