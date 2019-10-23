<?php

namespace App\Entity\Lead;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ActivityType
 *
 * @ORM\Entity(repositoryClass="App\Repository\Lead\ActivityTypeRepository")
 * @ORM\Table(name="tbl_lead_activity_type")
 * @Grid(
 *     api_lead_activity_type_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "at.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "at.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "default_status",
 *              "type"       = "string",
 *              "field"      = "ds.title"
 *          },
 *          {
 *              "id"         = "assign_to",
 *              "type"       = "boolean",
 *              "field"      = "at.assignTo"
 *          },
 *          {
 *              "id"         = "due_date",
 *              "type"       = "boolean",
 *              "field"      = "at.dueDate"
 *          },
 *          {
 *              "id"         = "reminder_date",
 *              "type"       = "boolean",
 *              "field"      = "at.reminderDate"
 *          },
 *          {
 *              "id"         = "cc",
 *              "type"       = "boolean",
 *              "field"      = "at.cc"
 *          },
 *          {
 *              "id"         = "sms",
 *              "type"       = "boolean",
 *              "field"      = "at.sms"
 *          },
 *          {
 *              "id"         = "facility",
 *              "type"       = "boolean",
 *              "field"      = "at.facility"
 *          },
 *          {
 *              "id"         = "contact",
 *              "type"       = "boolean",
 *              "field"      = "at.contact"
 *          },
 *          {
 *              "id"         = "amount",
 *              "type"       = "boolean",
 *              "field"      = "at.amount"
 *          },
 *          {
 *              "id"         = "editable",
 *              "type"       = "boolean",
 *              "field"      = "at.editable"
 *          },
 *          {
 *              "id"         = "deletable",
 *              "type"       = "boolean",
 *              "field"      = "at.deletable"
 *          },
 *          {
 *              "id"         = "categories",
 *              "sortable"   = false,
 *              "type"       = "json",
 *              "field"      = "categories"
 *          }
 *     }
 * )
 */
class ActivityType
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_activity_type_list",
     *     "api_lead_activity_type_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_lead_activity_type_add",
     *          "api_lead_activity_type_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_activity_type_add",
     *          "api_lead_activity_type_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=60)
     * @Groups({
     *     "api_lead_activity_type_list",
     *     "api_lead_activity_type_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $title;

    /**
     * @var ActivityStatus
     * @Assert\NotNull(message = "Please select a Default Status", groups={
     *          "api_lead_activity_type_add",
     *          "api_lead_activity_type_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\ActivityStatus", inversedBy="types", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_default_status", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_activity_type_grid",
     *     "api_lead_activity_type_list",
     *     "api_lead_activity_type_get"
     * })
     */
    private $defaultStatus;

    /**
     * @var bool
     * @ORM\Column(name="assign_to", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_lead_activity_type_grid",
     *     "api_lead_activity_type_list",
     *     "api_lead_activity_type_get"
     * })
     */
    private $assignTo;

    /**
     * @var bool
     * @ORM\Column(name="due_date", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_lead_activity_type_grid",
     *     "api_lead_activity_type_list",
     *     "api_lead_activity_type_get"
     * })
     */
    private $dueDate;

    /**
     * @var bool
     * @ORM\Column(name="reminder_date", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_lead_activity_type_grid",
     *     "api_lead_activity_type_list",
     *     "api_lead_activity_type_get"
     * })
     */
    private $reminderDate;

    /**
     * @var bool
     * @ORM\Column(name="cc", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_lead_activity_type_grid",
     *     "api_lead_activity_type_list",
     *     "api_lead_activity_type_get"
     * })
     */
    private $cc;

    /**
     * @var bool
     * @ORM\Column(name="sms", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_lead_activity_type_grid",
     *     "api_lead_activity_type_list",
     *     "api_lead_activity_type_get"
     * })
     */
    private $sms;

    /**
     * @var bool
     * @ORM\Column(name="facility", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_lead_activity_type_grid",
     *     "api_lead_activity_type_list",
     *     "api_lead_activity_type_get"
     * })
     */
    private $facility;

    /**
     * @var bool
     * @ORM\Column(name="contact", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_lead_activity_type_grid",
     *     "api_lead_activity_type_list",
     *     "api_lead_activity_type_get"
     * })
     */
    private $contact;

    /**
     * @var bool
     * @ORM\Column(name="amount", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_lead_activity_type_grid",
     *     "api_lead_activity_type_list",
     *     "api_lead_activity_type_get"
     * })
     */
    private $amount;

    /**
     * @var bool
     * @ORM\Column(name="is_editable", type="boolean", options={"default" = 1})
     * @Groups({
     *     "api_lead_activity_type_grid",
     *     "api_lead_activity_type_list",
     *     "api_lead_activity_type_get"
     * })
     */
    private $editable;

    /**
     * @var bool
     * @ORM\Column(name="is_deletable", type="boolean", options={"default" = 1})
     * @Groups({
     *     "api_lead_activity_type_grid",
     *     "api_lead_activity_type_list",
     *     "api_lead_activity_type_get"
     * })
     */
    private $deletable;

    /**
     * @var array $categories
     * @ORM\Column(name="categories", type="json_array", nullable=true)
     * @Assert\All({
     *     @Assert\Choice(
     *          callback={"App\Model\Lead\ActivityOwnerType","getValues"},
     *          groups={
     *              "api_lead_activity_type_add",
     *              "api_lead_activity_type_edit"
     *          }
     *     )
     * })
     * @Groups({
     *     "api_lead_activity_type_list",
     *     "api_lead_activity_type_get"
     * })
     */
    private $categories = [];

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Activity", mappedBy="type", cascade={"remove", "persist"})
     */
    private $activities;

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $title = preg_replace('/\s\s+/', ' ', $title);
        $this->title = $title;
    }

    /**
     * @return ActivityStatus|null
     */
    public function getDefaultStatus(): ?ActivityStatus
    {
        return $this->defaultStatus;
    }

    /**
     * @param ActivityStatus $defaultStatus
     */
    public function setDefaultStatus(ActivityStatus $defaultStatus): void
    {
        $this->defaultStatus = $defaultStatus;
    }

    /**
     * @return bool
     */
    public function isAssignTo(): bool
    {
        return $this->assignTo;
    }

    /**
     * @param bool $assignTo
     */
    public function setAssignTo(bool $assignTo): void
    {
        $this->assignTo = $assignTo;
    }

    /**
     * @return bool
     */
    public function isDueDate(): bool
    {
        return $this->dueDate;
    }

    /**
     * @param bool $dueDate
     */
    public function setDueDate(bool $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return bool
     */
    public function isReminderDate(): bool
    {
        return $this->reminderDate;
    }

    /**
     * @param bool $reminderDate
     */
    public function setReminderDate(bool $reminderDate): void
    {
        $this->reminderDate = $reminderDate;
    }

    /**
     * @return bool
     */
    public function isCc(): bool
    {
        return $this->cc;
    }

    /**
     * @param bool $cc
     */
    public function setCc(bool $cc): void
    {
        $this->cc = $cc;
    }

    /**
     * @return bool
     */
    public function isSms(): bool
    {
        return $this->sms;
    }

    /**
     * @param bool $sms
     */
    public function setSms(bool $sms): void
    {
        $this->sms = $sms;
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
    public function isContact(): bool
    {
        return $this->contact;
    }

    /**
     * @param bool $contact
     */
    public function setContact(bool $contact): void
    {
        $this->contact = $contact;
    }

    /**
     * @return bool
     */
    public function isAmount(): bool
    {
        return $this->amount;
    }

    /**
     * @param bool $amount
     */
    public function setAmount(bool $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        return $this->editable;
    }

    /**
     * @param bool $editable
     */
    public function setEditable(bool $editable): void
    {
        $this->editable = $editable;
    }

    /**
     * @return bool
     */
    public function isDeletable(): bool
    {
        return $this->deletable;
    }

    /**
     * @param bool $deletable
     */
    public function setDeletable(bool $deletable): void
    {
        $this->deletable = $deletable;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return ArrayCollection
     */
    public function getActivities(): ArrayCollection
    {
        return $this->activities;
    }

    /**
     * @param ArrayCollection $activities
     */
    public function setActivities(ArrayCollection $activities): void
    {
        $this->activities = $activities;
    }
}
