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
 * Class FacilityEvent
 *
 * @ORM\Entity(repositoryClass="App\Repository\FacilityEventRepository")
 * @ORM\Table(name="tbl_facility_event")
 * @Grid(
 *     api_admin_facility_event_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "fe.id"
 *          },
 *          {
 *              "id"         = "definition",
 *              "type"       = "string",
 *              "field"      = "ed.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "fe.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "start",
 *              "type"       = "date",
 *              "field"      = "fe.start"
 *          },
 *          {
 *              "id"         = "end",
 *              "type"       = "date",
 *              "field"      = "fe.end"
 *          },
 *          {
 *              "id"         = "all_day",
 *              "type"       = "boolean",
 *              "field"      = "fe.allDay"
 *          },
 *          {
 *              "id"         = "rsvp",
 *              "type"       = "boolean",
 *              "field"      = "fe.rsvp"
 *          },
 *          {
 *              "id"         = "repeat",
 *              "type"       = "enum",
 *              "field"      = "fe.repeat",
 *              "values"     = "\App\Model\RepeatType::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "repeat_end",
 *              "type"       = "date",
 *              "field"      = "fe.repeatEnd"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(fe.notes, 1, 100)), CASE WHEN LENGTH(fe.notes) > 100 THEN '…' ELSE '' END)"
 *          },
 *          {
 *              "id"         = "users",
 *              "sortable"   = false,
 *              "type"       = "json",
 *              "field"      = "users"
 *          },
 *          {
 *              "id"         = "residents",
 *              "sortable"   = false,
 *              "type"       = "json",
 *              "field"      = "residents"
 *          }
 *     }
 * )
 */
class FacilityEvent
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    private $id;

    /**
     * @var EventDefinition
     * @Assert\NotNull(message = "Please select a Definition", groups={
     *     "api_admin_facility_event_add",
     *     "api_admin_facility_event_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\EventDefinition", inversedBy="facilityEvents")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_definition", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    private $definition;

    /**
     * @var Facility
     * @Assert\NotNull(message = "Please select a Facility", groups={
     *     "api_admin_facility_event_add",
     *     "api_admin_facility_event_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Facility", inversedBy="facilityEvents")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    private $facility;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_event_add",
     *     "api_admin_facility_event_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_facility_event_add",
     *          "api_admin_facility_event_edit"
     * })
     * @ORM\Column(name="title", type="string", length=100)
     * @Groups({
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    private $title;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_event_add",
     *     "api_admin_facility_event_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_facility_event_add",
     *     "api_admin_facility_event_edit"
     * })
     * @ORM\Column(name="start", type="datetime")
     * @Groups({
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    private $start;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *     "api_admin_facility_event_add",
     *     "api_admin_facility_event_edit"
     * })
     * @ORM\Column(name="end", type="datetime", nullable=true)
     * @Groups({
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    private $end;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_facility_event_add",
     *          "api_admin_facility_event_edit"
     * })
     * @Groups({
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    private $notes;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="facilityEvents", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_facility_event_users",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_facility_event", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @Groups({
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    private $users;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Resident", inversedBy="facilityEvents", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_facility_event_residents",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_facility_event", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @Groups({
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    private $residents;

    /**
     * @var bool
     * @ORM\Column(name="all_day", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    protected $allDay;

    /**
     * @var int
     * @ORM\Column(name="repeat_type", type="smallint", nullable=true)
     * @Groups({
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    private $repeat;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *     "api_admin_facility_event_add",
     *     "api_admin_facility_event_edit"
     * })
     * @ORM\Column(name="repeat_end", type="datetime", nullable=true)
     * @Groups({
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    private $repeatEnd;

    /**
     * @var bool
     * @ORM\Column(name="rsvp", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_facility_event_list",
     *     "api_admin_facility_event_get"
     * })
     */
    protected $rsvp;

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
     * @return EventDefinition|null
     */
    public function getDefinition(): ?EventDefinition
    {
        return $this->definition;
    }

    /**
     * @param EventDefinition|null $definition
     */
    public function setDefinition(?EventDefinition $definition): void
    {
        $this->definition = $definition;
    }

    /**
     * @return Facility|null
     */
    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    /**
     * @param Facility|null $facility
     */
    public function setFacility(?Facility $facility): void
    {
        $this->facility = $facility;
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
        $this->title = $title;
    }

    /**
     * @return \DateTime|null
     */
    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     */
    public function setStart($start): void
    {
        $this->start = $start;
    }

    /**
     * @return \DateTime|null
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     */
    public function setEnd($end): void
    {
        $this->end = $end;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param $users
     */
    public function setUsers($users): void
    {
        $this->users = $users;
    }

    /**
     * @param User|null $user
     */
    public function addUser(?User $user): void
    {
        $this->users->add($user);
    }

    /**
     * @param User|null $user
     */
    public function removeUser(?User $user): void
    {
        $this->users->removeElement($user);
    }

    /**
     * @return mixed
     */
    public function getResidents()
    {
        return $this->residents;
    }

    /**
     * @param $residents
     */
    public function setResidents($residents): void
    {
        $this->residents = $residents;
    }

    /**
     * @param Resident|null $resident
     */
    public function addResident(?Resident $resident): void
    {
        $this->residents->add($resident);
    }

    /**
     * @param Resident|null $resident
     */
    public function removeResident(?Resident $resident): void
    {
        $this->residents->removeElement($resident);
    }

    /**
     * @return bool
     */
    public function isAllDay(): bool
    {
        return $this->allDay;
    }

    /**
     * @param bool $allDay
     */
    public function setAllDay(bool $allDay): void
    {
        $this->allDay = $allDay;
    }

    /**
     * @return int|null
     */
    public function getRepeat(): ?int
    {
        return $this->repeat;
    }

    /**
     * @param int|null $repeat
     */
    public function setRepeat(?int $repeat): void
    {
        $this->repeat = $repeat;
    }

    /**
     * @return \DateTime|null
     */
    public function getRepeatEnd(): ?\DateTime
    {
        return $this->repeatEnd;
    }

    /**
     * @param \DateTime|null $repeatEnd
     */
    public function setRepeatEnd(?\DateTime $repeatEnd): void
    {
        $this->repeatEnd = $repeatEnd;
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
}
