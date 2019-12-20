<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use App\Annotation\Grid;

/**
 * Class CorporateEvent
 *
 * @ORM\Entity(repositoryClass="App\Repository\CorporateEventRepository")
 * @ORM\Table(name="tbl_corporate_event")
 * @Grid(
 *     api_admin_corporate_event_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ce.id"
 *          },
 *          {
 *              "id"         = "definition",
 *              "type"       = "string",
 *              "field"      = "ed.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "done",
 *              "type"       = "boolean",
 *              "field"      = "ce.done"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "ce.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "start",
 *              "type"       = "date",
 *              "field"      = "ce.start"
 *          },
 *          {
 *              "id"         = "end",
 *              "type"       = "date",
 *              "field"      = "ce.end"
 *          },
 *          {
 *              "id"         = "all_day",
 *              "type"       = "boolean",
 *              "field"      = "ce.allDay"
 *          },
 *          {
 *              "id"         = "rsvp",
 *              "type"       = "boolean",
 *              "field"      = "ce.rsvp"
 *          },
 *          {
 *              "id"         = "repeat",
 *              "type"       = "enum",
 *              "field"      = "ce.repeat",
 *              "values"     = "\App\Model\RepeatType::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "repeat_end",
 *              "type"       = "date",
 *              "field"      = "ce.repeatEnd"
 *          },
 *          {
 *              "id"         = "no_repeat_end",
 *              "type"       = "boolean",
 *              "field"      = "ce.noRepeatEnd"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(ce.notes, 1, 100)), CASE WHEN LENGTH(ce.notes) > 100 THEN '…' ELSE '' END)"
 *          },
 *          {
 *              "id"         = "facilities",
 *              "sortable"   = false,
 *              "type"       = "json",
 *              "field"      = "facilities"
 *          },
 *          {
 *              "id"         = "roles",
 *              "sortable"   = false,
 *              "type"       = "json",
 *              "field"      = "roles"
 *          }
 *     }
 * )
 */
class CorporateEvent
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    private $id;

    /**
     * @var EventDefinition
     * @Assert\NotNull(message = "Please select a Definition", groups={
     *     "api_admin_corporate_event_add",
     *     "api_admin_corporate_event_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\EventDefinition", inversedBy="corporateEvents")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_definition", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    private $definition;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_corporate_event_add",
     *     "api_admin_corporate_event_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_corporate_event_add",
     *          "api_admin_corporate_event_edit"
     * })
     * @ORM\Column(name="title", type="string", length=100)
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    private $title;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_corporate_event_add",
     *     "api_admin_corporate_event_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_corporate_event_add",
     *     "api_admin_corporate_event_edit"
     * })
     * @ORM\Column(name="start", type="datetime")
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    private $start;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *     "api_admin_corporate_event_add",
     *     "api_admin_corporate_event_edit"
     * })
     * @ORM\Column(name="end", type="datetime", nullable=true)
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
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
     *          "api_admin_corporate_event_add",
     *          "api_admin_corporate_event_edit"
     * })
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    private $notes;

    /**
     * @var bool
     * @ORM\Column(name="all_day", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    protected $allDay;

    /**
     * @var int
     * @ORM\Column(name="repeat_type", type="smallint", nullable=true)
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    private $repeat;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *     "api_admin_corporate_event_add",
     *     "api_admin_corporate_event_edit"
     * })
     * @ORM\Column(name="repeat_end", type="datetime", nullable=true)
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    private $repeatEnd;

    /**
     * @var bool
     * @ORM\Column(name="rsvp", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    protected $rsvp;

    /**
     * @var bool
     * @ORM\Column(name="no_repeat_end", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    protected $noRepeatEnd;

    /**
     * @var bool
     * @ORM\Column(name="done", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    protected $done;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Facility", inversedBy="corporateEvents", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_corporate_event_facilities",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_corporate_event", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    private $facilities;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Role", inversedBy="corporateEvents", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_corporate_event_roles",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_corporate_event", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_role", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    private $roles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CorporateEventUser", mappedBy="event", cascade={"persist"})
     */
    private $corporateEventUsers;

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("users")
     * @Serializer\Groups({"api_admin_corporate_event_get", "api_admin_corporate_event_list"})
     */
    public function getUsers(): ?array
    {
        $users = [];
        if ($this->getCorporateEventUsers() !== null) {
            /** @var CorporateEventUser $corporateEventUser */
            foreach ($this->getCorporateEventUsers() as $corporateEventUser) {
                $users[] = $corporateEventUser->getUser();
            }
            return $users;
        }

        return null;
    }

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

    /**
     * @return bool
     */
    public function isNoRepeatEnd(): bool
    {
        return $this->noRepeatEnd;
    }

    /**
     * @param bool $noRepeatEnd
     */
    public function setNoRepeatEnd(bool $noRepeatEnd): void
    {
        $this->noRepeatEnd = $noRepeatEnd;
    }

    /**
     * @return bool
     */
    public function isDone(): bool
    {
        return $this->done;
    }

    /**
     * @param bool $done
     */
    public function setDone(bool $done): void
    {
        $this->done = $done;
    }

    /**
     * @return mixed
     */
    public function getFacilities()
    {
        return $this->facilities;
    }

    /**
     * @param $facilities
     */
    public function setFacilities($facilities): void
    {
        $this->facilities = $facilities;
    }

    /**
     * @param Facility|null $facility
     */
    public function addFacility(?Facility $facility): void
    {
        $this->facilities->add($facility);
    }

    /**
     * @param Facility|null $facility
     */
    public function removeFacility(?Facility $facility): void
    {
        $this->facilities->removeElement($facility);
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return mixed
     */
    public function getRoleObjects()
    {
        return $this->roles;
    }

    /**
     * @param $roles
     */
    public function setRoles($roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @param Role|null $role
     */
    public function addRole(?Role $role): void
    {
        $this->roles->add($role);
    }

    /**
     * @param Role|null $role
     */
    public function removeRole(?Role $role): void
    {
        $this->roles->removeElement($role);
    }

    /**
     * @return mixed
     */
    public function getCorporateEventUsers()
    {
        return $this->corporateEventUsers;
    }

    /**
     * @param mixed $corporateEventUsers
     */
    public function setCorporateEventUsers($corporateEventUsers): void
    {
        $this->corporateEventUsers = $corporateEventUsers;
    }
}
