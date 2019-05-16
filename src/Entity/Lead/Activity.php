<?php

namespace App\Entity\Lead;

use App\Entity\Facility;
use App\Entity\User;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class Activity
 *
 * @ORM\Entity(repositoryClass="App\Repository\Lead\ActivityRepository")
 * @ORM\Table(name="tbl_lead_activity")
 * @Grid(
 *     api_lead_activity_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "a.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "a.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "owner_type",
 *              "type"       = "enum",
 *              "field"      = "a.ownerType",
 *              "values"     = "\App\Model\Lead\ActivityOwnerType::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "type",
 *              "type"       = "string",
 *              "field"      = "at.title"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "datetime",
 *              "field"      = "a.date"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "a.notes"
 *          },
 *          {
 *              "id"         = "status",
 *              "type"       = "string",
 *              "field"      = "st.title"
 *          },
 *          {
 *              "id"         = "assign_to",
 *              "type"       = "string",
 *              "field"      = "u.assignTo"
 *          },
 *          {
 *              "id"         = "due_date",
 *              "type"       = "datetime",
 *              "field"      = "a.dueDate"
 *          },
 *          {
 *              "id"         = "reminder_date",
 *              "type"       = "datetime",
 *              "field"      = "a.reminderDate"
 *          },
 *          {
 *              "id"         = "facility",
 *              "type"       = "string",
 *              "field"      = "f.name"
 *          },
 *          {
 *              "id"         = "referral",
 *              "type"       = "string",
 *              "field"      = "r.title"
 *          },
 *          {
 *              "id"         = "organization",
 *              "type"       = "string",
 *              "field"      = "o.title"
 *          }
 *     }
 * )
 */
class Activity
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_lead_activity_edit",
     *          "api_lead_referral_activity_edit",
     *          "api_lead_organization_activity_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_lead_activity_edit",
     *          "api_lead_referral_activity_edit",
     *          "api_lead_organization_activity_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=100)
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $title;

    /**
     * @var int
     * @ORM\Column(name="owner_type", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\Lead\ActivityOwnerType","getTypeValues"},
     *     groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add"
     *     }
     * )
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $ownerType;

    /**
     * @var ActivityType
     * @Assert\NotNull(message = "Please select a Type", groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\ActivityType", inversedBy="activities", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_type", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $type;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_lead_activity_edit",
     *          "api_lead_referral_activity_edit",
     *          "api_lead_organization_activity_edit"
     * })
     * @Assert\DateTime(groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_lead_activity_edit",
     *          "api_lead_referral_activity_edit",
     *          "api_lead_organization_activity_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $date;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_lead_activity_edit",
     *          "api_lead_referral_activity_edit",
     *          "api_lead_organization_activity_edit"
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $notes;

    /**
     * @var ActivityStatus
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\ActivityStatus", inversedBy="activities", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_status", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $status;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="leadActivities", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="assign_to", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $assignTo;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_lead_activity_edit",
     *          "api_lead_referral_activity_edit",
     *          "api_lead_organization_activity_edit"
     * })
     * @ORM\Column(name="due_date", type="datetime", nullable=true)
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $dueDate;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_lead_activity_edit",
     *          "api_lead_referral_activity_edit",
     *          "api_lead_organization_activity_edit"
     * })
     * @ORM\Column(name="reminder_date", type="datetime", nullable=true)
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $reminderDate;

    /**
     * @var Facility
     * @ORM\ManyToOne(targetEntity="App\Entity\Facility", inversedBy="leadActivities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $facility;

    /**
     * @var Referral
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Referral", inversedBy="activities")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_referral", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a Referral", groups={
     *          "api_lead_referral_activity_add",
     *          "api_lead_referral_activity_edit"
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $referral;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Organization", inversedBy="activities")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_organization", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select an Organization", groups={
     *          "api_lead_organization_activity_add",
     *          "api_lead_organization_activity_edit"
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $organization;

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
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
        $title = preg_replace('/\s\s+/', ' ', $title);
        $this->title = $title;
    }

    /**
     * @return int|null
     */
    public function getOwnerType(): ?int
    {
        return $this->ownerType;
    }

    /**
     * @param int|null $ownerType
     */
    public function setOwnerType(?int $ownerType): void
    {
        $this->ownerType = $ownerType;
    }

    /**
     * @return ActivityType|null
     */
    public function getType(): ?ActivityType
    {
        return $this->type;
    }

    /**
     * @param ActivityType|null $type
     */
    public function setType(?ActivityType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return null|string
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param null|string $notes
     */
    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    /**
     * @return ActivityStatus|null
     */
    public function getStatus(): ?ActivityStatus
    {
        return $this->status;
    }

    /**
     * @param ActivityStatus|null $status
     */
    public function setStatus(?ActivityStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @return User|null
     */
    public function getAssignTo(): ?User
    {
        return $this->assignTo;
    }

    /**
     * @param User|null $assignTo
     */
    public function setAssignTo(?User $assignTo): void
    {
        $this->assignTo = $assignTo;
    }

    /**
     * @return \DateTime
     */
    public function getDueDate(): \DateTime
    {
        return $this->dueDate;
    }

    /**
     * @param \DateTime $dueDate
     */
    public function setDueDate(\DateTime $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return \DateTime
     */
    public function getReminderDate(): \DateTime
    {
        return $this->reminderDate;
    }

    /**
     * @param \DateTime $reminderDate
     */
    public function setReminderDate(\DateTime $reminderDate): void
    {
        $this->reminderDate = $reminderDate;
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
     * @return Referral|null
     */
    public function getReferral(): ?Referral
    {
        return $this->referral;
    }

    /**
     * @param Referral|null $referral
     */
    public function setReferral(?Referral $referral): void
    {
        $this->referral = $referral;
    }

    /**
     * @return Organization|null
     */
    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    /**
     * @param Organization|null $organization
     */
    public function setOrganization(?Organization $organization): void
    {
        $this->organization = $organization;
    }
}
